<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Cart;
use App\Services\MercadoPagoService;
use App\Services\OrderNotifier;
use App\Support\LojaRetiradaConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function __construct(
        protected Cart $cart,
        protected MercadoPagoService $mercadoPago,
        protected OrderNotifier $notifier,
    ) {
    }

    public function index()
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('store.index')
                ->with('error', 'Seu carrinho está vazio.');
        }

        return view('checkout.index', [
            'items' => $this->cart->items(),
            'total' => $this->cart->total(),
            'mercadoPagoEnabled' => $this->mercadoPago->isConfigured(),
            'retirada' => [
                'local' => LojaRetiradaConfig::local(),
                'instrucoes' => LojaRetiradaConfig::instrucoes(),
                'horarios' => LojaRetiradaConfig::horariosAtivos(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('store.index')
                ->with('error', 'Seu carrinho está vazio.');
        }

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'regex:/^\(\d{2}\) \d{5}-\d{4}$/'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'customer_phone.regex' => 'Informe o telefone no formato (99) 99999-9999.',
        ]);

        $order = DB::transaction(function () use ($data) {
            $order = Order::create([
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'notes' => $data['notes'] ?? null,
                'total' => $this->cart->total(),
                'status' => 'pendente',
            ]);

            foreach ($this->cart->items() as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'size' => $item['size'],
                    'unit_price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);
            }

            return $order;
        });

        $order->load('items');

        $this->notifier->notifyAdmin($order);
        $this->cart->clear();

        if ($this->mercadoPago->isConfigured()) {
            $order->update([
                'payment_method' => 'mercadopago',
                'payment_status' => 'pending',
            ]);
            $this->notifier->notifyCustomerCreated($order);

            return redirect()->route('checkout.payment', $order);
        }

        $order->update(['payment_method' => 'manual']);
        $this->notifier->notifyCustomerCreated($order);

        return redirect()->route('checkout.success', ['ref' => $order->reference]);
    }

    public function payment(Order $order)
    {
        if ($order->pagamentoConfirmado()) {
            return redirect()->route('checkout.success', ['ref' => $order->reference]);
        }

        if (! $this->mercadoPago->isConfigured()) {
            return redirect()->route('checkout.pending', ['ref' => $order->reference])
                ->with('error', 'Pagamento online indisponível no momento.');
        }

        $order->load('items');

        $payerEmail = $order->customer_email;
        if ($this->mercadoPago->isSandbox()
            && ! str_ends_with(strtolower((string) $payerEmail), '@testuser.com')
        ) {
            $local = preg_replace('/[^a-z0-9._-]/', '', strtolower(\Illuminate\Support\Str::before((string) $payerEmail, '@'))) ?: 'buyer';
            $payerEmail = $local.'@testuser.com';
        }

        return view('checkout.payment', [
            'order' => $order,
            'publicKey' => $this->mercadoPago->publicKey(),
            'amount' => round((float) $order->total, 2),
            'sandbox' => $this->mercadoPago->isSandbox(),
            'payerEmail' => $payerEmail,
            'retirada' => [
                'local' => LojaRetiradaConfig::local(),
                'instrucoes' => LojaRetiradaConfig::instrucoes(),
                'horarios' => LojaRetiradaConfig::horariosAtivos(),
            ],
        ]);
    }

    public function processPayment(Request $request, Order $order)
    {
        if ($order->pagamentoConfirmado()) {
            return response()->json([
                'ok' => true,
                'status' => 'approved',
                'redirect' => route('checkout.success', ['ref' => $order->reference]),
            ]);
        }

        if (! $this->mercadoPago->isConfigured()) {
            return response()->json(['ok' => false, 'error' => 'Mercado Pago não configurado.'], 422);
        }

        $formData = $request->all();
        $order->loadMissing('items');

        $result = $this->mercadoPago->createOrder($order, $formData);

        if (! ($result['ok'] ?? false)) {
            return response()->json([
                'ok' => false,
                'error' => $result['error'] ?? 'Falha no pagamento.',
            ], 422);
        }

        $status = (string) ($result['status'] ?? 'pending');
        $paymentId = $result['mp_order_id'] ?? $result['payment_id'] ?? null;
        $statusDetail = (string) ($result['status_detail'] ?? '');

        $updates = [
            'payment_method' => 'mercadopago',
            'payment_status' => $status,
            'payment_id' => $paymentId ? (string) $paymentId : null,
        ];

        if ($status === 'approved' && ! $order->pagamentoConfirmado()) {
            $updates['status'] = Order::statusPosPagamento();
        }

        $order->update($updates);

        if (in_array($status, ['rejected', 'cancelled'], true)) {
            return response()->json([
                'ok' => false,
                'status' => $status,
                'payment_id' => $paymentId,
                'error' => $this->mensagemRecusaPagamento(['status_detail' => $statusDetail]),
                'retry_url' => route('checkout.payment', $order),
            ], 422);
        }

        $redirect = match ($status) {
            'approved' => route('checkout.success', ['ref' => $order->reference]),
            default => route('checkout.pending', ['ref' => $order->reference]),
        };

        return response()->json([
            'ok' => true,
            'status' => $status,
            'payment_id' => $paymentId,
            'mp_order_id' => $result['mp_order_id'] ?? null,
            'redirect' => $redirect,
            'pix' => $result['pix'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payment
     */
    protected function mensagemRecusaPagamento(array $payment): string
    {
        $detail = (string) ($payment['status_detail'] ?? '');

        return match ($detail) {
            'cc_rejected_bad_filled_card_number' => 'Número do cartão inválido. Confira e tente novamente.',
            'cc_rejected_bad_filled_date' => 'Data de validade inválida.',
            'cc_rejected_bad_filled_security_code' => 'Código de segurança (CVV) inválido.',
            'cc_rejected_insufficient_amount' => 'Cartão sem limite suficiente.',
            'cc_rejected_call_for_authorize' => 'Pagamento precisa de autorização. Contate o banco do cartão.',
            'cc_rejected_duplicated_payment' => 'Pagamento duplicado. Aguarde alguns minutos e tente de novo.',
            'cc_rejected_high_risk' => 'Pagamento recusado por segurança.',
            'cc_rejected_blacklist' => 'Cartão não pôde ser processado.',
            'cc_rejected_other_reason' => 'Cartão recusado. Em testes, use os cartões oficiais do Mercado Pago (ex.: Mastercard 5031 4332 1540 6351, CVV 123, validade 11/30).',
            default => 'Pagamento não aprovado. Tente outro cartão ou Pix.',
        };
    }

    public function success(Request $request)
    {
        $order = $this->resolveOrder($request);

        if ($order && $request->filled('payment_id')) {
            $approved = $request->query('status') === 'approved';

            if ($approved && ! $order->pagamentoConfirmado()) {
                $order->update([
                    'payment_method' => 'mercadopago',
                    'payment_status' => $request->query('status', 'approved'),
                    'payment_id' => $request->query('payment_id'),
                    'status' => Order::statusPosPagamento(),
                ]);
            } else {
                $order->update([
                    'payment_method' => 'mercadopago',
                    'payment_status' => $request->query('status', $order->payment_status),
                    'payment_id' => $request->query('payment_id'),
                ]);
            }
        }

        $this->cart->clear();

        return view('checkout.result', [
            'type' => 'success',
            'order' => $order,
        ]);
    }

    public function failure(Request $request)
    {
        return view('checkout.result', [
            'type' => 'failure',
            'order' => $this->resolveOrder($request),
        ]);
    }

    public function pending(Request $request)
    {
        $order = $this->resolveOrder($request);
        $this->cart->clear();

        return view('checkout.result', [
            'type' => 'pending',
            'order' => $order,
        ]);
    }

    protected function resolveOrder(Request $request): ?Order
    {
        $reference = $request->query('ref') ?? $request->query('external_reference');

        return $reference ? Order::where('reference', $reference)->first() : null;
    }
}

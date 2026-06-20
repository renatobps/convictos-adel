<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Cart;
use App\Services\MercadoPagoService;
use App\Services\OrderNotifier;
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

        // Avisa o administrador assim que o pedido é criado.
        $this->notifier->notifyAdmin($order);

        if ($this->mercadoPago->isConfigured()) {
            $checkoutUrl = $this->mercadoPago->createPreference($order);

            if ($checkoutUrl) {
                return redirect()->away($checkoutUrl);
            }
        }

        // Sem MercadoPago configurado: registra como pedido manual.
        $order->update(['payment_method' => 'manual']);
        $this->notifier->confirmCustomer($order);
        $this->cart->clear();

        return redirect()->route('checkout.success', ['ref' => $order->reference]);
    }

    public function success(Request $request)
    {
        $order = $this->resolveOrder($request);

        if ($order && $request->filled('payment_id')) {
            $wasPaid = $order->status === 'pago';
            $approved = $request->query('status') === 'approved';

            $order->update([
                'payment_method' => 'mercadopago',
                'payment_status' => $request->query('status', 'approved'),
                'payment_id' => $request->query('payment_id'),
                'status' => $approved ? 'pago' : $order->status,
            ]);

            // Confirmação ao cliente apenas quando o pagamento é aprovado (uma vez).
            if ($approved && ! $wasPaid) {
                $this->notifier->confirmCustomer($order);
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

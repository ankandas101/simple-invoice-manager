<?php

namespace App\Actions\Tec;

use App\Models\Invoice;
use App\Models\TaxRate;
use Illuminate\Support\Facades\DB;

class PrepareOrder
{
    protected $data;

    public function __construct(protected $form, protected $model, protected $updating = false)
    {
        $taxes = TaxRate::all();
        foreach ($this->form['items'] as &$item) {
            $price = $item['price'];
            $item['tax_amount'] = 0;
            $item['discount_amount'] = 0;
            if ($item['discount'] ?? null) {
                $item['discount_amount'] = calculateDiscount($item['discount'], $price);
                $price = $price - $item['discount_amount'];
            }
            if (($item['taxes'] ?? null) && ! empty($item['taxes'])) {
                $item_taxes = $taxes->filter(fn ($tax) => in_array($tax->id, $item['taxes']));
                $item['tax_amount'] = calculateTaxes($item_taxes, $price, $item['tax_method']);
            }
            if ($item['tax_method'] == 'inclusive') {
                $item['net_price'] = formatDecimal($price - $item['tax_amount']);
                $item['unit_price'] = $price;
            } else {
                $item['net_price'] = formatDecimal($price);
                $item['unit_price'] = formatDecimal($price + $item['tax_amount']);
            }
            $item['total_discount_amount'] = formatDecimal($item['discount_amount'] * $item['quantity']);
            $item['total_tax_amount'] = formatDecimal($item['tax_amount'] * $item['quantity']);
            $item['total'] = formatDecimal($item['unit_price'] * $item['quantity']);
            $item['subtotal'] = formatDecimal($item['net_price'] * $item['quantity']);
        }

        $this->form['items'] = collect($this->form['items']);
        $this->form['total'] = $this->form['items']->sum('total');
        $this->form['subtotal'] = $this->form['items']->sum('subtotal');
        $this->form['product_discount_amount'] = $this->form['items']->sum('total_discount_amount');
        $this->form['product_tax_amount'] = $this->form['items']->sum('total_tax_amount');
        $this->form['order_discount'] = $this->form['discount'] ?? null;
        $this->form['order_taxes'] = $this->form['taxes'] ?? null;

        $this->form['order_discount_amount'] = 0;
        $this->form['order_tax_amount'] = 0;

        $total = $this->form['total'];
        if ($this->form['order_discount']) {
            $this->form['order_discount_amount'] = calculateDiscount($this->form['discount'], $total);
            $total = $total - $this->form['order_discount_amount'];
        }
        if ($this->form['order_taxes'] && ! empty($this->form['order_taxes'])) {
            $order_taxes = $taxes->filter(fn ($tax) => in_array($tax->id, $this->form['order_taxes']));
            $this->form['order_tax_amount'] = calculateTaxes($order_taxes, $total, $this->form['tax_method']);
        }

        $this->form['total_discount_amount'] = formatDecimal($this->form['product_discount_amount'] + $this->form['order_discount_amount']);
        $this->form['total_tax_amount'] = formatDecimal($this->form['product_tax_amount'] + $this->form['order_tax_amount']);
        $this->form['grand_total'] = formatDecimal($total + ($this->form['tax_method'] == 'exclusive' ? $this->form['order_tax_amount'] : 0));
        if ($this->model instanceof Invoice) {
            $this->form['last_created_at'] = $this->form['date'];
        }
        $this->form['grand_total'] += ($this->form['shipping'] ?? null) ? $this->form['shipping'] : 0;
    }

    public function getForm()
    {
        return $this->form;
    }

    public function save()
    {
        DB::transaction(function () {
            $grand_total = 0;
            if ($this->updating) {
                $this->model->items()->forceDelete();
                $status = $this->model->status;
                $grand_total = $this->model->grand_total;
                if ($this->model instanceof Invoice && ($grand_total != $this->form['grand_total'] || $this->form['status'] == 'canceled')) {
                    $this->model->customer->transactions()->create([
                        'amount'     => 0 - $grand_total,
                        'type'       => ($this->form['status'] == 'canceled' ? 'invoice-canceled' : 'invoice-edit'),
                        'invoice_id' => $this->model->id,
                        'account_id' => $this->model->account_id,
                    ]);
                }
            }
            $this->model->fill($this->form)->save();
            $this->model->taxes()->sync($this->form['order_taxes'] ?? []);
            foreach ($this->form['items'] as $item) {
                $ii = $this->model->items()->create($item);
                $ii->taxes()->sync($item['taxes'] ?? []);
            }
            if ($this->model instanceof Invoice) {
                $this->model->refresh();
                if ($this->updating) {
                    if ($grand_total != $this->form['grand_total'] || ($status == 'canceled' && $this->model->status != 'canceled')) {
                        $this->model->customer->transactions()->create([
                            'amount'     => $this->form['grand_total'],
                            'type'       => 'invoice-updated',
                            'invoice_id' => $this->model->id,
                            'account_id' => $this->model->account_id,
                        ]);
                    }
                } elseif ($this->model->status != 'canceled') {
                    $this->model->customer->transactions()->create([
                        'amount'     => $this->form['grand_total'],
                        'type'       => 'invoice-created',
                        'invoice_id' => $this->model->id,
                        'account_id' => $this->model->account_id,
                    ]);
                }
            }
            $this->model->refresh();
        }, 2);

        return $this->model;
    }
}

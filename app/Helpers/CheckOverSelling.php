<?php

namespace App\Helpers;

use App\Models\Item;

class CheckOverSelling
{
    public function check($order_items)
    {
        $error = [];
        $items = Item::whereIn('id', collect($order_items)->pluck('item_id'))->get();
        foreach ($order_items as $key => $order_item) {
            $quantity = $order_item['quantity'] + 0;
            $old_quantity = $order_item['old_quantity'] ?? 0;
            $item = $items->where('id', $order_item['item_id'])->first();
            $quantity = $quantity - $old_quantity;

            if ($item->quantity < $quantity) {
                $error["items.{$key}.quantity"] = __('{name} have only {available} quantity available.', ['name' => $order_item['name'], 'available' => ((float) $item->quantity)]);
            }
        }

        return $error;
    }
}

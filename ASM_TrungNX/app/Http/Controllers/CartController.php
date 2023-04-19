<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\CartItem;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = Session::get('products.cart', []);
        $totalPrice = $this->getTotalPrice($cartItems);
        return view('products.cart', compact('cartItems', 'totalPrice'));
    }

    
    public function count(){
        $cartItems = Session::get('products.cart', []);
        $cartItemCount = count($cartItems);
        return $cartItemCount;
    }
    

    public function add(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $cartItems = Session::get('products.cart', []);

        // Tìm kiếm sản phẩm trong giỏ hàng với ID tương ứng
        $itemId = array_search($id, array_column($cartItems, 'product_id'));

        if ($itemId !== false) {
            // Nếu sản phẩm đã có trong giỏ hàng, tăng số lượng sản phẩm đó lên
            $cartItems[$itemId]['quantity']++;
        } else {
            // Nếu sản phẩm không có trong giỏ hàng, thêm sản phẩm mới vào giỏ hàng
            $newItemId = count($cartItems) > 0 ? max(array_column($cartItems, 'id')) + 1 : 1;
            $cartItem = [
                'id' => $newItemId,
                'product_id' => $product->id,
                'name' => $product->prodname,
                'quantity' => 1,
                'price' => $product->price,
            ];
            array_push($cartItems, $cartItem);
        }

        Session::put('products.cart', $cartItems);

        return redirect()->route('products.cart')->with('success', 'Product added to cart.');
    }


    public function remove($id)
    {
        $cartItems = Session::get('products.cart', []);

        foreach ($cartItems as $key => $item) {
            if ($item['id'] == $id) {
                unset($cartItems[$key]);
                break;
            }
        }

        Session::put('products.cart', $cartItems);

        return redirect()->route('products.cart')->with('success', 'Product removed from cart.');
    }

    private function getItemId($productId, $cartItems)
    {
        foreach ($cartItems as $key => $item) {
            if ($item['product_id'] == $productId) {
                return $key;
            }
        }
        return false;
    }

    private function getTotalPrice($cartItems)
    {
        $totalPrice = 0;
        foreach ($cartItems as $item) {
            $totalPrice += $item['price'] * $item['quantity'];
        }
        return $totalPrice;
    }
}

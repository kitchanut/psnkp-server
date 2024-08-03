<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Purchase_details;
use App\Models\Stock_part;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Purchase::with(['user', 'branch'])->where('po_active', 1)->get());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $purchases = $request->except(['purchase_details', 'id']);
        $purchasesCrate = Purchase::create($purchases);
        $purchasesDetails = $request->purchase_details;
        foreach ($purchasesDetails as $key => $purchasesDetail) {
            if ($purchasesDetail['type'] == 1) {
                $purchasesDetail['car_id'] = 0;
            }
            $purchasesDetail['purchase_id'] = $purchasesCrate->id;
            $createPurchase_detail = Purchase_details::create($purchasesDetail);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function show(Purchase $purchase)
    {
        $purchase->purchase_details;
        return response()->json($purchase);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function edit(Purchase $purchase)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Purchase $purchase)
    {
        $purchase->update($request->except(['purchase_details', 'updated_at']));
        Purchase_details::where('purchase_id', $purchase->id)->delete();
        $purchasesDetails = $request->purchase_details;
        foreach ($purchasesDetails as $key => $purchasesDetail) {
            $purchasesDetail['purchase_id'] = $purchase->id;
            $createPurchase_details = Purchase_details::create($purchasesDetail);
            if ($request->po_active == 2) {
                $queryStock = Stock_part::where('car_part_id', $purchasesDetail['car_part_id'])
                    ->where('branch_id', $request->branch_id)
                    ->first();

                if ($queryStock == null) {
                    $queryStock =  Stock_part::create(['car_part_id' => $purchasesDetail['car_part_id'], 'car_part_amount' => $purchasesDetail['car_part_amount'], 'branch_id' => $request->branch_id]);
                } else {
                    // return response()->json($queryStock['car_part_amount']);
                    $addvalue =  $queryStock['car_part_amount'] + $purchasesDetail['car_part_amount'];
                    $queryStock->car_part_amount = $addvalue;
                    $queryStock->save();
                }
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function destroy(Purchase $purchase)
    {
        $purchase->delete();
        Purchase_details::where('purchase_id', $purchase->id)->delete();
        // return response()->json();
    }

    public function where_po_number($po_number)
    {
        return response()->json(Purchase::where('purchases.po_number', 'like', $po_number . '%')->get());
    }
}

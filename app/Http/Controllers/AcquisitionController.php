public function loadMaterials(Request $request)
{
$request->validate([
'requisition_id' => 'required|exists:purchase_requisitions,requisition_id'
]);

$requisition = PurchaseRequisition::where('requisition_id', $request->requisition_id)
->with('items')
->first();

if (!$requisition) {
return response()->json([
'success' => false,
'message' => 'Requisition not found'
]);
}

$materials = $requisition->items->map(function ($item) {
$totalAcquired = AcquiredItem::whereHas('acquired', function ($query) use ($item) {
$query->where('purchase_requisition_id', $item->purchase_requisition_id);
})->where('purchase_item_id', $item->id)->sum('quantity');

return [
'id' => $item->id,
'description' => $item->item_description,
'requested' => $item->quantity,
'acquired' => $totalAcquired,
'balance' => $item->quantity - $totalAcquired
];
})->filter(function ($material) {
return $material['balance'] > 0;
});

return response()->json([
'success' => true,
'materials' => $materials,
'requisition' => $requisition
]);
}
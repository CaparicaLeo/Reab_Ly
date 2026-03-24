<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTreatmentItemRequest;
use App\Http\Requests\UpdateTreatmentItemRequest;
use App\Models\Treatment;
use App\Models\TreatmentItem;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TreatmentItemController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(Treatment $treatment)
    {
        $this->authorize('viewAny', $treatment);

        return response()->json($treatment->items);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTreatmentItemRequest $request, Treatment $treatment)
    {
        $this->authorize('create', [TreatmentItem::class, $treatment]);
        return response()->json(
            TreatmentItem::create(['treatment_id' => $treatment->id] + $request->validated()),
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(TreatmentItem $treatmentItem)
    {
        $treatmentItem->load('treatment');
        $this->authorize('view', $treatmentItem);

        return response()->json($treatmentItem);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTreatmentItemRequest $request, TreatmentItem $treatmentItem)
    {
        $this->authorize('update', $treatmentItem);

        $treatmentItem->update($request->validated());

        return response()->json($treatmentItem);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TreatmentItem $treatmentItem)
    {
        $this->authorize('delete', $treatmentItem);

        $treatmentItem->delete();

        return response()->json(null, 204);
    }
}

<?php

namespace Modules\Location\app\Http\Controllers\Api\V1;


use App\Http\Controllers\Api\V1\Controller;
use App\Interfaces\LocationManageInterface;
use Illuminate\Http\Request;
use Modules\Location\app\Http\Requests\StateRequest;
use Modules\Location\app\Models\State;
use Modules\Location\app\Transformers\StateResource;

class StateController extends Controller
{

    public function __construct(protected LocationManageInterface $locationRepo)
    {
    }

    /**
     * List all states (paginated, with optional filters).
     */
    public function states(Request $request)
    {
        $states = State::paginate($request->per_page ?? 15);

        return response()->json([
            'data' => StateResource::collection($states->items()),
            'meta' => [
                'total'        => $states->total(),
                'per_page'     => $states->perPage(),
                'current_page' => $states->currentPage(),
                'last_page'    => $states->lastPage(),
                'from'         => $states->firstItem(),
                'to'           => $states->lastItem(),
            ],
        ]);
    }

    /**
     * Create a new state.
     */
    public function statesAdd(StateRequest $request)
    {
        $state = State::create($request->validated());
        // translations save
        saveTranslations($request, $state);

        return response()->json([
            'status' => true,
            'message' => 'State created successfully.',
        ], 201);
    }

    /**
     * Show a single state with its cities.
     */
    public function statesDetails($id)
    {
       $state = State::with(['cities.areas','translations'])
           ->where('id', (int)$id)
           ->first();

        return response()->json([
            'data' => new StateResource($state)
        ]);
    }

    public function statesUpdate(StateRequest $request)
    {
        $state = State::findOrFail((int)$request->id);
        $state->update($request->validated());
        saveTranslations($request, $state);

        return response()->json([
            'message' => 'State updated successfully.',
        ],200);
    }

    /**
     * Soft-delete a state.
     */
    public function statesDelete(Request $request)
    {
        $state = State::findOrFail($request->id);
        $state->translations()->delete();
        $state->forceDelete();

        return response()->json([
            'message' => 'State deleted successfully.'
        ]);
    }

    /**
     * Toggle active status.
     */
    public function statesUpdateStatus(Request $request)
    {
        State::where('id', (int)$request->id)->update([
            'is_active' => (boolean)$request->is_active
        ]);

        return response()->json([
            'message'   => 'Status updated.',
        ]);
    }
}

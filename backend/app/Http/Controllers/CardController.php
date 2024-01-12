<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CardController extends Controller
{

    public function index()
    {
        $columns = Card::orderBy('order')->get();

        return response()->json($columns);
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'column_id' => 'required|exists:columns,id',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validation passed, attempt to create column
        $data = $request->only('title', 'description', 'column_id');


        // Find the maximum order value for the given column
        $maxOrder = Card::where('column_id', $data['column_id'])->max('order');

        // Increment the order for the new card
        $data['order'] = $maxOrder + 1;


        $card = Card::create($data);

        return response()->json($card, 201);
    }

    public function show($id)
    {
        $card = Card::find($id);

        // Check if the card exists
        if (!$card) {
            return response()->json(['message' => 'Card not found'], 404);
        }
        return response()->json($card);
    }

    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);


        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $card = Card::find($id);

        // Check if the card exists
        if (!$card) {
            return response()->json(['message' => 'Card not found'], 404);
        }

        $data = $request->only('title', 'description');

        $card->update($data);

        return response()->json($card, 200);
    }

    public function destroy($id)
    {


        $card = Card::withTrashed()->find($id);
        if (!$card) {
            return response()->json(['message' => 'Card not found'], 404);
        }
        if ($card->trashed()) {
            $card->forceDelete();
            return response()->json(['message' => 'Card permanently deleted'], 204);
        }
        $card->delete();


        return response()->json(['message' => 'Card deleted'], 204);
    }


    public function moveCard(Request $request)
    {
        if($request->column == 'same'){
            $this->moveCardToSameColumn($request);
        }
        else{
            $this->moveCardToOtherColumn($request);
        }
    }

    public function moveCardToSameColumn($request)
    {


        
        $cards = Card::where('column_id', $request->columnId)->orderBy('order')->get();
        $cardWillMoveDown = $cards[$request->newIndex];
        $newOrder = $cardWillMoveDown->order;
        if ($request->newIndex < $request->oldIndex) {
            $cardWillMoveDown->order = $cardWillMoveDown->order + 1;
        }
        if ($request->newIndex > $request->oldIndex) {
            $cardWillMoveDown->order = $cardWillMoveDown->order - 1;
        }


        $cardWillMoveDown->update();


        Card::where('id', $request->carId)->update(['order' => $newOrder]);
        $cards = Card::where('column_id', $request->columnId)->orderBy('order')->get();

        if ($request->newIndex < $request->oldIndex) {
            foreach ($cards as $index => $card) {
                if (($card->order >= $cardWillMoveDown->order) && ($card->id !=  $cardWillMoveDown->id)) {

                    Card::where('id', $card->id)->update(['order' => $card->order + 1]);
                }
            }
        }
        if ($request->newIndex > $request->oldIndex) {
            foreach ($cards as $index => $card) {
                if (($card->order <= $cardWillMoveDown->order) && ($card->id !=  $cardWillMoveDown->id)) {

                    Card::where('id', $card->id)->update(['order' => $card->order - 1]);
                }
            }
        }



        return response()->json(['message' => 'Card moved successfully']);
    }

    public function moveCardToOtherColumn($request)
    {

        $oldColumncards = Card::where('column_id', $request->old_column_id)->orderBy('order')->get();
        $newColumncards = Card::where('column_id', $request->new_column_id)->orderBy('order')->get();
        if (($newColumncards->count() == 0)) {
            Card::where('id', $request->card_id)->update(['column_id' => $request->new_column_id]);
        } else if (($newColumncards->count() == $request->new_index)) {
            Card::where('id', $request->card_id)->update(['column_id' => $request->new_column_id, 'order' => (($newColumncards[$request->new_index - 1]->order) + 1)]);
        } else {

            $cardNewPositionAt = $newColumncards[$request->new_index];

            $cardUpdated = false;
            foreach ($oldColumncards as $oldColumncard) {

                if ($oldColumncard->id == $request->card_id) {
                    $oldColumncard->order = $cardNewPositionAt->order;
                    $oldColumncard->column_id = $cardNewPositionAt->column_id;
                    $oldColumncard->update();
                    $cardUpdated = true;
                }
                if ($cardUpdated) {
                    $oldColumncard->order = $oldColumncard->order - 1;
                }
            }

            $newColumncards = Card::where('column_id', $request->new_column_id)->orderBy('order')->get();
            foreach ($newColumncards as  $card) {
                if (($card->order >= $cardNewPositionAt->order) && ($card->id !=  $request->card_id)) {

                    Card::where('id', $card->id)->update(['order' => $card->order + 1]);
                }
            }
        }



        return response()->json(['message' => 'Card moved successfully']);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Column;
use Illuminate\Support\Facades\Validator;

class ColumnController extends Controller
{
    public function index()
    {
        $columns = Column::with(['cards' => function ($query) {
            $query->orderBy('order');
        }])
        ->orderBy('created_at')->get();

        return response()->json($columns);
    }

    public function store(Request $request)
    {
       
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
        ]);
    
        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        // Validation passed, attempt to create column
        $data = $request->only('title');

        

        $column = Column::create($data);

        return response()->json($column, 201);
    }

    public function destroy($id)
    {
        $column = Column::find($id);
        if (!$column) {
            return response()->json(['message' => 'column not found'], 404);
        }
        $column->cards()->delete();
        $column->delete();

        return response()->json(['message' => 'column deleted'], 204);
    }
}

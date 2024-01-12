<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Column;
use Illuminate\Support\Facades\Validator;

class ColumnController extends Controller
{
    public function index($status = 1,$date=null)
    {
        // return($date);
        if ($status == 1) {
            $columns = Column::with(['cards' => function ($query) use ($date) {
                if($date){
                    $query->whereDate('created_at', $date)->orderBy('order');
                }else{
                    $query->orderBy('order');
                }
                
            }])
                ->orderBy('created_at')->get();
        } else if ($status == 0) {
            $columns = Column::with(['cards' => function ($query) use ($date) {
                if($date){
                    $query->whereDate('created_at', $date)->onlyTrashed()->orderBy('order');
                }
                else{
                    $query->onlyTrashed()->orderBy('order');
                }
                
            }])
                ->orderBy('created_at')->get();
        } else if ($status === 'all') {
            $columns = Column::with(['cards' => function ($query) use ($date) {
                if($date){
                    $query->whereDate('created_at', $date)->withTrashed()->orderBy('order');
                }
                else{
                    $query->withTrashed()->orderBy('order');
                }
               
            }])
                ->orderBy('created_at')->get();
        }


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

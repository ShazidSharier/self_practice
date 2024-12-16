<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts\select;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('student.index');
    }
    public function getStudentData()
    {
        // $student = DB::table('student_details')->get();
        // return response()->json([
        //     'data' => $student
        //    ]);
        // die();
       $student = DB::table('student_details')
       ->join('student_addresses', 'student_details.id', '=', 'student_addresses.student_details_id')
       ->join('address_types', 'student_addresses.address_types_id', '=', 'address_types.id')
       ->select(
        'student_details.id',
        'student_details.name',
        'student_details.code',
        'student_details.email',
        'student_details.mobile',
        'student_details.dob',
        'student_details.created_at',
        DB::raw('GROUP_CONCAT(CONCAT(address_types.type, ":", student_addresses.address_details) SEPARATOR "</br>") as address_details')
       )
       ->whereIn('student_details.id',function($query){
            $query->select(DB::raw('MAX(student_details.id)'))
            ->from('student_details')
            ->groupBy('student_details.name');
       })
        ->groupBy(
            'student_details.id',
            'student_details.name',
            'student_details.code',
            'student_details.email',
            'student_details.mobile',
            'student_details.dob',
            'student_details.created_at',
        )->get();
       return response()->json([
        'data' => $student
       ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data['form_type'] = 'Create';
        $data['address_types'] = DB::table('address_types')->get();
        return view('student.create',$data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $newCode = DB::table('student_details')->orderBy('id', 'desc')->value('code');
        $serialCode = $newCode ? intval($newCode) +1:1;
        $studentCode = str_pad($serialCode,6,'0',STR_PAD_LEFT);
        while (DB::table('student_details')->where('code',$studentCode)->exists()){
            $serialCode++;
            $studentCode = str_pad($serialCode, 6, '0', STR_PAD_LEFT);
        }
        DB::beginTransaction();
        try {
            $student = DB::table('student_details')->insertGetId([
                'name'=> $request->name,
                'email' => $request->email,
                'code' => $studentCode,
                'mobile' => $request->mobile,
                'dob' => $request->dob,
                'users_id'=> Auth::id(),
                'created_at' => now(),
            ]);
            foreach($request->address_types as $key => $value)
            DB::table('student_addresses')->insert([
                'users_id' => Auth::id(),
                'address_types_id' => $value,
                'student_details_id' => $student,
                'address_details' => $request->address_details[$key],
                'created_at' => now(),
            ]);

            DB::commit();
            return redirect()->route('student.index')->with('message', 'Store SuccessFully');
        } catch (\Exception $e) {
            return $e;
            DB::rollBack();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data['form_type'] = 'Edit';
        $data['address_types'] = DB::table('address_types')->get();
        $data['student_details'] = DB::table('student_details')->where('id',$id)->first();
        $data['student_addresses'] = DB::table('student_addresses')
        ->join('address_types', 'student_addresses.address_types_id', '=', 'address_types.id')
        ->select(
            'student_addresses.id',
            'address_types.type',
            'student_addresses.address_details',
            'student_addresses.address_types_id',
        )
        ->where('student_addresses.student_details_id',$id)->get();
        return view('student.create',$data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // dd($id);
        DB::beginTransaction();
        try {
            $student = DB::table('student_details')->where('id',$id)->first();
            if (!$student) {
                return response()->json([
                    'message' => 'Invalid'
                ]);
            }
            DB::table('student_marks')->where('student_details_id', $id)->delete();
            DB::table('student_addresses')->where('student_details_id', $id)->delete();
            DB::table('student_details')->where('id',$id)->delete();

            DB::commit();
            // return response()->json([
            //     'message' => ' Delete Successfully'
            // ]);
            return back()->with('message', 'Delete Successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\t_spkl_detail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\TSpklDetailResource;

class TSpklDetailController extends Controller{

    public function index()
    {
        //get all spkl detail
        $t_spkl_details = t_spkl_detail::latest()->paginate(20);

        //return collection of  spkl detail as a resource
        return new TSpklDetailResource(true, 'List Data SPKL Detail', $t_spkl_details);
    }

    public function store(Request $request)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'title'     => 'required',
            'content'   => 'required',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //create
        $t_spkl_detail = t_spkl_detail::create([
            'title'     => $request->title,
            'content'   => $request->content,
        ]);

        //return response
        return new TSpklDetailResource(true, 'Data SPKL Detail Berhasil Ditambahkan!', $t_spkl_detail);
    }

    public function show($id)
    {
        $t_spkl_detail = t_spkl_detail::find($id);   //find spkl detail by ID

        return new TSpklDetailResource(true, 'Detail Data SPKL Detail!', $t_spkl_detail);   //return single spkl detail as a resource
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [  //define validation rules
            'title'     => 'required',
            'content'   => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);  //check if validation fails
        }

        //find spkl detail by ID
            $t_spkl_detail = t_spkl_detail::find($id);
            $t_spkl_detail->update([
                'title'     => $request->title,
                'content'   => $request->content,
            ]);

        return new TSpklDetailResource(true, 'Data SPKL Detail Berhasil Diubah!', $t_spkl_detail); //return response
    }

    public function destroy($id)
    {
        $t_spkl_detail = t_spkl_detail::find($id); //find spkl detail by ID
        $t_spkl_detail->delete();  //delete spkl detail

        //return response
        return new TSpklDetailResource(true, 'Data SPKL Detail Berhasil Dihapus!', null);
    }


    public function search(Request $request)
    {
        $query = DB::table('t_spkl_details')
            ->join('m_employees', 't_spkl_details.npk', '=', 'm_employees.npk')
            ->join('m_sub_sections', 'm_employees.npk', '=', 'm_sub_sections.code')
            ->join('m_departments', 'm_sub_sections.code_department', '=', 'm_departments.name')
            ->select(
                'm_employees.nama as nama',
                'm_employees.npk as npk',
                'm_sub_sections.code as code',
                'm_departments.name as name',
                'm_department.code as code_department',
                't_spkl_details.start_date as start_date',
                't_spkl_details.end_date as end_date',
                't_spkl_details.start_planning as start_planning',
                't_spkl_details.end_planning as end_planning'
            );

        // Filter by start_date if provided
        if ($request->has('start_date') && !empty($request->input('start_date'))) {
            $query->where('t_spkl_details.start_date', $request->input('start_date'));
        }

        // Filter by npk if provided
        if ($request->has('npk') && !empty($request->input('npk'))) {
            $query->where('m_employees.npk', $request->input('npk'));
        }

        $results = $query->get()
            ->map(function ($item) {
                return [
                    'nama' => $item->nama,
                    'npk' => $item->npk,
                    'start_date' => $item->start_date,
                    'end_date' => $item->end_date,
                    'start_planning' => $item->start_planning,
                    'end_planning' => $item->end_planning,
                    'sub_section' => $item->sub_section,
                    'code_section' => $item->code_section,
                    'code_department' => $item->code_department,
                    'department_name' => $item->name,
                ];
            });

        return response()->json([
            'status' => true,
            'message' => 'Detail Data SPKL!',
            'data' => $results,
        ]);
    }
}

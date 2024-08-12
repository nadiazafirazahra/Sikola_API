<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InnerJoinController extends Controller
{
    public function getJoinedData(Request $request)
    {
        // Mengambil parameter dari query string
        $npk = $request->query('npk');
        $start_date = $request->query('start_date');
        $department = $request->query('department');
        $section = $request->query('section');
        $sub_section = $request->query('sub_section');

        // Validasi format tanggal jika disediakan
        if ($start_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) {
            return response()->json([
                'status' => false,
                'message' => 'Tanggal format is invalid. Use YYYY-MM-DD',
                'data' => null
            ], 400);
        }
            Log::info('Start Date: ' . $start_date);
            Log::info('Department: ' . $department);

            DB::listen(function ($query) {
                Log::info('SQL: ' . $query->sql);
                Log::info('Bindings: ' . json_encode($query->bindings));
                Log::info('Time: ' . $query->time);
            });


            $query = DB::table('m_employees')
                ->leftJoin('t_spkl_details', 'm_employees.npk', '=', 't_spkl_details.npk')
                ->leftJoin('m_sub_sections', 'm_employees.sub_section', '=', 'm_sub_sections.code')
                ->leftJoin('m_sections', 'm_sub_sections.code_section', '=', 'm_sections.code')
                ->leftJoin('m_departments', 'm_sections.code_department', '=', 'm_departments.code')
                ->select(
                    'm_employees.nama as Nama',
                    'm_employees.npk as NPK',
                    // 'm_sub_sections.code as Code Sub Section',
                    'm_sub_sections.name as Sub Section',
                    // 'm_sections.code as Code Section',
                    'm_sections.name as Section',
                    // 'm_sections.code_department as Code Department',
                    'm_departments.name as Department',
                    't_spkl_details.start_date as Start Date',
                    't_spkl_details.end_date as End Date',
                    't_spkl_details.start_planning as Start Planning',
                    't_spkl_details.end_planning as End Planning'
                );

            // Apply filters if provided
            if ($npk) {
                $query->where('m_employees.npk', $npk);
            }

            if ($start_date) {
                $query->whereDate('t_spkl_details.start_date', $start_date);
            }

            if ($department) {
                $query->where('m_departments.name', $department);
            }

            if ($section) {
                $query->where('m_sections.name', $section);
            }

            if ($sub_section) {
                $query->where('m_sub_sections.name', $sub_section);
            }


            // Run the query and get the results
            $results = $query->get();

            // Group the results by start_date
            $groupedResults = $results->groupBy('start_date');

            // Return JSON response
            return response()->json([
                'status' => true,
                'message' => 'Detail Data SPKL!',
                'data' => $groupedResults,
            ]);

        }
    }

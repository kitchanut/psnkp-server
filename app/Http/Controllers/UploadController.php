<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $credentials = $request->all();
        $output = Upload::where([['ref_id', $credentials['id']], ['type', $credentials['type']]])->get();
        return response()->json($output);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $credentials = (array) json_decode($data['formData']);
        if ($request->hasFile('files')) {
            $files = $request->file('files');
            foreach ($files as $file) {
                $path = $file->store('files');
                $dataFile['type'] = $credentials['type'];
                $dataFile['ref_id'] = $credentials['id'];
                $dataFile['name'] = $file->getClientOriginalName();
                $dataFile['extension'] = $file->getClientOriginalExtension();
                $dataFile['size'] = $file->getSize();
                $dataFile['path'] = $path;
                Upload::create($dataFile);
            }
        }
        return response()->json([
            'status' => true,
            'data' => $credentials
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Upload  $upload
     * @return \Illuminate\Http\Response
     */
    public function show(Upload $upload)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Upload  $upload
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Upload $upload)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Upload  $upload
     * @return \Illuminate\Http\Response
     */
    public function destroy(Upload $upload)
    {
        Storage::delete($upload->path);
        $upload->delete();
    }
}

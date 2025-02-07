<?php

namespace App\Http\Controllers\Admin;

use App\Models\Option;
use App\Models\Product;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\OptionRequest;

class OptionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $options = Option::with([
        'product' => function ($prod) { $prod->select('id'); },
        'attribute' => function ($attr) { $attr->select('id'); }
        ])->select('id', 'product_id', 'attribute_id', 'price')->paginate(PAGINATION_COUNT);

        return view('admin.options.options', compact('options'));
    }

    // ------------------------------------------------------//

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = [];
        // active is scope in Product Model
        $data['products'] = Product::active()->select('id')->get();
        $data['attributes'] = Attribute::select('id')->get();

        return view('admin.options.createOptions', $data);
    }

    // ------------------------------------------------------//

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(OptionRequest $request)
    {
        // return $request;

        try {

            //validation

            DB::beginTransaction();

            $option = Option::create([
                'attribute_id' => $request->attribute_id,
                'product_id' => $request->product_id,
                'price' => $request->price,
            ]);

            //save translations

            $option->name = $request->name;

            $option->save();

            DB::commit();

            return redirect()->route('admin.options')->with(['success' => 'تم الحفظ بنجاح']);

        }

        catch (\Exception $ex) {

            DB::rollback();

            return redirect()->route('admin.options')->with(['error' => 'حدث خطا ما برجاء المحاوله لاحقا']);

        }
    }

    // ------------------------------------------------------//

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    // ------------------------------------------------------//

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = [];
        $data['option'] = Option::find($id);

        if (!$data['option'])
            return redirect()->route('admin.options')->with(['error' => 'هذه القيمة غير موجوده ']);

        $data['products'] = Product::active()->select('id')->get();
        $data['attributes'] = Attribute::select('id')->get();

        return view('admin.options.editOptions', $data);
    }

    // ------------------------------------------------------//

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(OptionRequest $request, $id)
    {
        try {

            //validation

            $option = Option::find($id);

            if (!$option)
                return redirect()->route('admin.options')->with(['error' => 'هذا العنصر غير موجود']);

            DB::beginTransaction();

            //update DB

            $option->update($request->only(['price','product_id','attribute_id']));

            // $attribute ->update($request->except('_token', 'id'));

            //save translations

            $option->name = $request->name;

            $option->save();

            DB::commit();

            return redirect()->route('admin.options')->with(['success' => 'تم ألتحديث بنجاح']);

        }

        catch (\Exception $ex) {

            DB::rollback();

            return redirect()->route('admin.options')->with(['error' => 'حدث خطا ما برجاء المحاوله لاحقا']);

        }
    }

    // ------------------------------------------------------//

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {

            $option = Option::find($id);

            if (!$option)
                return redirect()->route('admin.options')->with(['error' => 'هذة الخاصية غير موجودة']);

            $option->delete();

            return redirect()->route('admin.options')->with(['success' => 'تم  الحذف بنجاح']);

        }

        catch (\Exception $ex) {

            return redirect()->route('admin.options')->with(['error' => 'حدث خطا ما برجاء المحاوله لاحقا']);

        }
    }

    // ------------------------------------------------------//
}

<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Photo;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $data = array(
            'products' => Product::orderBy('updated_at', 'desc')->paginate(30),
            'categories' => Category::all(),
        );
        return view('product.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {


        $data = array(
            'categories' => Category::all()
        );
        return view('product.create')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    { 
     
        $messages = [
            'required' => 'O campo :attribute  é obrigatório',
            'max' => 'O campo :attribute não pode ter mais de :max caractéres',
            'min' => 'O campo :attribute  deve ter no mínimo :min caractéres',
            'image' => ':attribute precisa ser uma imagem',
            'file' => 'Falhou o upload do ficheiro',
            'image.*.max' => 'A(s) imagem(s) não pode ter mais de 700 Kilobytes',
            'image.*.image' => 'A(s) imagem(s) devem ser imagens',
        ];
        $rules = [
            'name' => 'required|max:255',
            'description' => 'max:40000',
            'price' => 'required|min:0',
            'image.*' => 'file|image|max:700',
            'category_id' => 'required',
        ];
        $attributes = [
            'name' => 'nome',
            'description' => 'descrição',
            'price' => 'preço',
            'image' => 'imagem(s)',
            'category_id' => 'categoria'
        ];

        $validator = Validator::make($request->all(), $rules, $messages, $attributes);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $product = new Product();
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->whatsapp_number = $request->whatsapp_number;
        $product->phone_number = $request->phone_number;
        $product->sale = $request->sale;
        $product->user_id = auth()->user()->id;
        $product->category_id =   $request->category_id;
        $product->save();
        
        if($request->hasfile('image')){
            foreach($request->file('image') as $file){
                $filename = Str::random(4) . time() . '.' . $file->getClientOriginalExtension();
                $path = 'public/img/' . $filename;
                Storage::disk('local')->put($path, file_get_contents($file));
                $photo = new Photo();
                $photo->product_id = $product->id;
                $photo->path = 'storage/img/' . $filename;
                $photo->save();
            }
        }
        $request->session()->flash('activity', 'Produto:  ' . $product->name . ' criado');
        return response()->json('OK', 200);
        return redirect('/admin/product');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::findOrFail($id);
        $data = array(
            'product' => $product,
        );
        return view('product.show')->with($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = array(
            'categories' => Category::all(),
            'product' => Product::findOrfail($id)
        );
        return view('product.edit')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $messages = [
            'required' => 'O campo :attribute  é obrigatório',
            'max' => 'O campo :attribute não pode ter mais de :max caractéres',
            'min' => 'O campo :attribute  deve ter no mínimo :min caractéres',
            'image' => ':attribute precisa ser uma imagem',
            'file' => 'Falhou o upload do ficheiro',
            'image.*.size' => 'A(s) imagem(s) não pode ter mais de 700Kilobytes',
            'image.*.image' => 'A(s) imagem(s) devem ser imagens',
        ];
        $rules = [
            'name' => 'required|max:255',
            'description' => 'max:40000', 
            'price' => 'required|min:0',
            'category_id' => 'required',
        ];
        $attributes = [
            'sale' => 'desconto',
            'name' => 'nome',
            'description' => 'descrição',
            'price' => 'preço',
            'image' => 'imagem(s)',
            'category_id' => 'categoria'
        ];

        $validator = Validator::make($request->all(), $rules, $messages, $attributes);
        $validator->errors();
        
       
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $product = Product::findOrFail($id);

    

        $product->name = $request->name;
        if (strlen($request->description) > 11) {
            $product->description = $request->description;
        }
        $product->whatsapp_number = $request->whatsapp_number;
        $product->phone_number = $request->phone_number;
        $product->price = $request->price;
        $product->sale = $request->sale;
        $product->user_id = auth()->user()->id;
        $product->category_id =   $request->category_id;
        $product->save();


        $request->session()->flash('activity', 'Produto:  ' . $product->name . ' editado');

        return redirect('/admin/product/' . $product->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $deletePath = Str::replaceFirst('storage', 'public', $product->photo_path);
        foreach ($product->photos as $photo) {
            $deletePath = Str::replaceFirst('storage', 'public', $photo->path);
            Storage::disk('local')->delete($deletePath);
        }
        $product->reviews()->delete();
        $product->photos()->delete();
        $product->destroy($id);
        session()->flash('activity', 'Producto de nome: ' . $product->name . ' apagado com sucesso');
        return redirect('/admin/product');
    }

    public function search(Request $request)
    {
        //Retorna productos de acordo com o nome
        if ($request->searchText != null && $request->category == "") {
            $products = Product::where('name', 'LIKE', '%' . $request->searchText . '%')->orderBy('updated_at', 'desc')->paginate(50);
        } //Retorna productos de acordo com a categoria
        else if ($request->searchText == null && $request->category != "") {
            $products = Product::where('category_id', $request->category)->orderBy('updated_at', 'desc')->paginate(50);
        } //Retorna productos de acordo com a categoria e o nome
        else if ($request->searchText != null && $request->category != "") {
            $products = Product::where([
                ['category_id', $request->category],
                ['name', 'LIKE', '%' . $request->searchText . '%']
            ])
                ->orderBy('updated_at', 'desc')
                ->paginate(50);
        } else
        //Retorna productos todos os produtos, pois não têm parametros
        {
            $products = Product::orderBy('updated_at', 'desc')->paginate(50);
        }
        $data = array(
            'products' => $products,
            'categories' => Category::all(),
        );
        return view('product.index')->with($data);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:191',
            'metaTitle' => 'required|max:191',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'metaDescription' => 'nullable|string|max:255',
            'metaKeywords' => 'nullable|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ]);
        } else {
            $validatedData = $validator->validated();
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('categories', 'public');
                $validatedData['image'] = $imagePath;
            }

            // Create a new category using the validated data
            $category = Category::create($validatedData);

            // Return a response indicating the category was created successfully
            return response()->json([
                'status' => 200,
                'message' => 'Category created successfully',
                'category' => $category,
            ], 201);
        }
    }

    public function index()
    {
        $categories = Category::all()->map(function ($category) {
            $category->image = Storage::url($category->image);
            return $category;
        });

        return response()->json([
            'status' => 200,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'max:191',
            'metaTitle' => 'max:191',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'metaDescription' => 'nullable|string|max:255',
            'metaKeywords' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            \Log::error('Validation errors:', $validator->messages()->toArray());
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ]);
        }

        $category = Category::find($id);
        if (!$category) {
            \Log::error('Category not found:', ['id' => $id]);
            return response()->json([
                'status' => 404,
                'message' => 'Category not found',
            ], 404);
        }

        $validatedData = $validator->validated();
        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }

            $imagePath = $request->file('image')->store('categories', 'public');
            $validatedData['image'] = $imagePath;
        }

        $category->update($validatedData);

        \Log::info('Updated category:', $category->toArray());

        $category->image = Storage::url($category->image);

        return response()->json([
            'status' => 200,
            'message' => 'Category updated successfully',
            'category' => $category,
        ]);
    }

    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                'status' => 404,
                'message' => 'Category not found',
            ], 404);
        }

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Category deleted successfully',
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::withCount('items')
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('categories.index', compact('categories'));
    }

    public function autocomplete(Request $request)
    {
        $search = trim((string) $request->query('search'));

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $categories = Category::withCount('items')
            ->where('name', 'like', "%{$search}%")
            ->orderBy('name')
            ->limit(10)
            ->get();

        return response()->json($categories->map(fn ($category) => [
            'value' => $category->name,
            'title' => $category->name,
            'subtitle' => $category->items_count.' barang',
        ]));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'description' => ['nullable', 'string'],
        ]);

        $category = Category::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'id' => $category->id,
                'name' => $category->name,
            ]);
        }

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = array_filter(array_map('intval', (array) $request->input('selected_items', [])));

        if (empty($ids)) {
            return back()->with('error', 'Pilih minimal satu kategori untuk dihapus.');
        }

        $categories = Category::whereIn('id', $ids)->get();
        $deletedCount = 0;
        $skippedCount = 0;

        foreach ($categories as $category) {
            if ($category->items()->exists()) {
                $skippedCount++;
                continue;
            }

            $category->delete();
            $deletedCount++;
        }

        $message = 'Kategori berhasil dihapus.';

        if ($deletedCount > 0 && $skippedCount > 0) {
            $message = "Kategori yang aman dihapus: {$deletedCount}. Kategori yang dilewati karena masih memiliki barang: {$skippedCount}.";
        } elseif ($deletedCount === 0 && $skippedCount > 0) {
            $message = 'Tidak ada kategori yang bisa dihapus. Semua kategori yang dipilih masih memiliki barang.';
        }

        return redirect()->route('categories.index')->with('success', $message);
    }

    public function destroy(Category $category)
    {
        if ($category->items()->exists()) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena masih memiliki barang.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}

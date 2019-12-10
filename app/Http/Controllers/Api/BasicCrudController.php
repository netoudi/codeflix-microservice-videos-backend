<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class BasicCrudController extends Controller
{
    abstract protected function model(): string;

    abstract protected function rulesStore(): array;

    abstract protected function rulesUpdate(): array;

    public function index()
    {
        return $this->model()::all();
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rulesStore());

        $model = $this->model()::create($validatedData);
        $model->refresh();

        return $model;
    }

    public function show($id)
    {
        return $this->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $this->validate($request, $this->rulesUpdate());

        $model = $this->findOrFail($id);
        $model->update($validatedData);

        return $model;
    }

    public function destroy($id)
    {
        $model = $this->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }

    protected function findOrFail($id)
    {
        $model = $this->model();
        $keyName = (new $model())->getRouteKeyName();

        return $this->model()::where($keyName, $id)->firstOrFail();
    }
}

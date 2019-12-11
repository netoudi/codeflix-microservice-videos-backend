<?php

namespace App\Http\Controllers\Api;

use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends BasicCrudController
{
    /**
     * @var array
     */
    private $rules = [];

    public function __construct()
    {
        $this->rules = [
            'title' => 'required|max:255',
            'description' => 'required',
            'year_launched' => 'required|date_format:Y',
            'opened' => 'boolean',
            'rating' => 'required|in:' . implode(',', Video::RATING_LIST),
            'duration' => 'required|integer',
            'categories_id' => 'required|array|exists:categories,id',
            'genres_id' => 'required|array|exists:genres,id',
        ];
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rulesStore());

        /** @var Video $model */
        $model = \DB::transaction(function () use ($request, $validatedData) {
            $model = $this->model()::create($validatedData);
            $model->categories()->sync($request->get('categories_id'));
            $model->genres()->sync($request->get('genres_id'));
            $model->refresh();

            return $model;
        });

        return $model;
    }

    public function update(Request $request, $id)
    {
        $validatedData = $this->validate($request, $this->rulesUpdate());

        /** @var Video $model */
        $model = $this->findOrFail($id);
        $model->update($validatedData);
        $model->categories()->sync($request->get('categories_id'));
        $model->genres()->sync($request->get('genres_id'));

        return $model;
    }

    protected function model(): string
    {
        return Video::class;
    }

    protected function rulesStore(): array
    {
        return $this->rules;
    }

    protected function rulesUpdate(): array
    {
        return $this->rules;
    }
}

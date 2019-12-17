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
            'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL',
            'genres_id' => 'required|array|exists:genres,id,deleted_at,NULL',
        ];
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rulesStore());

        $self = $this;

        /** @var Video $model */
        $model = \DB::transaction(function () use ($request, $validatedData, $self) {
            $model = $this->model()::create($validatedData);
            $self->handleRelations($model, $request);
            $model->refresh();

            return $model;
        });

        return $model;
    }

    public function update(Request $request, $id)
    {
        $validatedData = $this->validate($request, $this->rulesUpdate());

        $self = $this;

        /** @var Video $model */
        $model = $this->findOrFail($id);
        $model = \DB::transaction(function () use ($request, $validatedData, $self, $model) {
            $model->update($validatedData);
            $self->handleRelations($model, $request);

            return $model;
        });

        return $model;
    }

    protected function handleRelations(Video $video, Request $request)
    {
        $video->categories()->sync($request->get('categories_id'));
        $video->genres()->sync($request->get('genres_id'));
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

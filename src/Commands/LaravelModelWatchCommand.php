<?php

namespace Mha\LaravelModelWatch\Commands;

use Illuminate\Database\Console\DatabaseInspectionCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Mha\LaravelModelWatch\Collections\BaseWatchCollection;
use Mha\LaravelModelWatch\ModelWatcher;

class LaravelModelWatchCommand extends DatabaseInspectionCommand
{
    protected $signature = 'model:watch
        {modelOrCollection=default : the model class or the name of a collection in your config}
        {id? : if modelOrCollection is a model name, specify the ID you want to watch}
        {--field= : specify which field(s) to show}
        {--interval=500 : How often (in milliseconds to poll the database for changes)}
    ';

    protected $description = 'Watch an Eloquent model for changes';

    protected int $intervalMilliseconds = 2500;

    public function handle(): int
    {
        $this->ensureDependenciesExist();
        $this->intervalMilliseconds = $this->option('interval');
        $models = $this->getModels();
        if ($models->isEmpty()) {
            $this->error('No models found');

            return self::INVALID;
        }

        $modelWatchers = $models->map(function (Model $model) {
            return ModelWatcher::create(
                $model,
                $this->output->getOutput()->section()
            );
        });

        while (true) {
            $modelWatchers->each->refresh();
            usleep(($this->intervalMilliseconds) * 1000);
        }
    }

    public function getModels(): Collection
    {
        if ($this->argument('id')) {
            $class = $this->qualifyModel($this->argument('modelOrCollection'));

            /** @var class-string<Model> $class */
            return collect([
                $class::findOrFail(
                    $this->argument('id'),
                    !empty($this->option('field')) ? $this->option('field') : [ '*' ]
                ),
            ]);
        }

        $collectionClass = $this->qualifyCollection($this->argument('modelOrCollection'));
        $this->info('Using collection class: '.$collectionClass);
        /** @var BaseWatchCollection $collection */
        $collection = app()->make($collectionClass);

        return $collection->getModels();
    }

    /**
     * Qualify the given model class base name.
     *
     * @param  string  $model
     * @return string
     *
     * @see \Illuminate\Console\GeneratorCommand
     */
    protected function qualifyModel(string $model): string
    {
        if (str_contains($model, '\\') && class_exists($model)) {
            return $model;
        }

        $model = ltrim($model, '\\/');

        $model = str_replace('/', '\\', $model);

        $rootNamespace = $this->laravel->getNamespace();

        if (Str::startsWith($model, $rootNamespace)) {
            return $model;
        }

        return is_dir(app_path('Models'))
            ? $rootNamespace.'Models\\'.$model
            : $rootNamespace.$model;
    }

    /**
     * Qualify the given collection class base name.
     *
     * @param  string  $model
     * @return string
     */
    protected function qualifyCollection(string $collection): string
    {
        if ($collection === 'default') {
            $collection = config('model-watch.collections.default');
        }

        if (str_contains($collection, '\\') && class_exists($collection)) {
            return $collection;
        }

        $collection = ltrim($collection, '\\/');

        $collection = str_replace('/', '\\', $collection);

        $rootNamespace = $this->laravel->getNamespace();

        if (Str::startsWith($collection, $rootNamespace)) {
            return $collection;
        }

        return $rootNamespace.'Collections\ModelWatch\\'.$collection;
    }
}

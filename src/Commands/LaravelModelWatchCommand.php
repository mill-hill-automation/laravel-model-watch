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

    protected Collection $modelWatchers;

    protected string $collectionName = '';

    public function handle(): int
    {
        $this->ensureDependenciesExist();
        $this->intervalMilliseconds = $this->option('interval');
        $this->modelWatchers = new Collection;

        $info = $this->output->getOutput()->section();
        while (true) {
            $this->loadModelWatchers($this->getModels());
            $this->modelWatchers->each->refresh();
            $info->overwrite(
                '<info>Watching '.$this->modelWatchers->count()
                .' models from '.$this->collectionName
                .'</info>'
            );
            usleep(($this->intervalMilliseconds) * 1000);
        }
    }

    /**
     * Given a collection of models, creates a ModelWatcher for each
     * and adds it to our ModelWatchers collection. If a model already
     * has a ModelWatcher it will be skipped.
     */
    public function loadModelWatchers(Collection $models): self
    {
        $models->each(function (Model $model) {
            $key = get_class($model).collect($model->getKey())->join(',');
            if ($this->modelWatchers->has($key)) {
                return;
            }
            $this->modelWatchers->put(
                $key,
                ModelWatcher::create(
                    $model, $this->output->getOutput()->section()
                )
            );
        });

        return $this;
    }

    public function getModels(): Collection
    {
        if ($this->argument('id')) {
            $class = $this->qualifyModel($this->argument('modelOrCollection'));
            $this->collectionName = $class.':'.$this->argument('id');

            /** @var class-string<Model> $class */
            return collect([
                $class::findOrFail(
                    $this->argument('id'),
                    ! empty($this->option('field')) ? $this->option('field') : ['*']
                ),
            ]);
        }

        $collectionClass = $this->qualifyCollection($this->argument('modelOrCollection'));
        $this->collectionName = $collectionClass;
        /** @var BaseWatchCollection $collection */
        $collection = app()->make($collectionClass);

        return $collection->getModels();
    }

    /**
     * Qualify the given model class base name.
     *
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

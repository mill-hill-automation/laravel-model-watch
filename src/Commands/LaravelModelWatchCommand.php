<?php

namespace Mha\LaravelModelWatch\Commands;

use Doctrine\DBAL\Schema\Column;
use Illuminate\Database\Console\DatabaseInspectionCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Mha\LaravelModelWatch\Collections\BaseWatchCollection;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

class LaravelModelWatchCommand extends DatabaseInspectionCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'model:watch
        {model|collection=default : the model class or the name of a collection in your config}
        {id? : the ID of the model to show. Required if model is specified.}
        {--field=* : specify which field(s) to show}
        {--interval=500 : How often (in milliseconds to poll the database for changes)}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Watch an Eloquent model for changes';

    protected Collection $fields;

    protected Model $model;

    protected int $intervalMilliseconds = 2500;

    protected ConsoleSectionOutput $section;

    public function handle(): int
    {
        $this->ensureDependenciesExist();
        $this->intervalMilliseconds = $this->option('interval');
        $models = $this->getModels();
        if ($models->isEmpty()) {
            $this->error('No models found');
            return self::INVALID;
        }

        // We don't support multiple models.... yet!
        $this->model = $models->first();

        while (true) {
            $this->addVersion($this->model->refresh()->toArray());
            $this->output();
            usleep(($this->intervalMilliseconds) * 1000);
        }
    }

    protected function getSection(): ConsoleSectionOutput
    {
        if (! isset($this->section)) {
            $this->section = $this->output->getOutput()->section();
        }

        return $this->section;
    }

    public function getModels(): Collection
    {
        if ($this->argument('id')) {
            $class = $this->qualifyModel($this->argument('model'));

            /** @var class-string<Model> $class */
            return collect([$class::findOrFail($this->argument('id'))]);
        }

        $collectionClass = config('model-watch.collections.default');
        ray($collectionClass);
        /** @var BaseWatchCollection $collection */
        $collection = app()->make($collectionClass);
        ray($collection);
        return $collection->getModels();

    }

    /**
     * Ask the database for all the columns on the table for the watched model,
     * and filters them according to --field options if given.
     */
    protected function getFields(): Collection
    {
        if (! isset($this->fields)) {
            // Get the list of available fields from the database
            $schema = $this->model->getConnection()->getDoctrineSchemaManager();
            $table = $this->model->getConnection()->getTablePrefix().$this->model->getTable();
            $columns = collect($schema->listTableColumns($table))->map(
                fn (Column $column) => $column->getName()
            );

            // If the user has specified some fields, make sure they are all valid.
            if ($this->option('field')) {
                $fields = collect($this->option('field'));
                $invalidFields = $fields->diff($columns);
                throw_if (
                    $invalidFields->isNotEmpty(),
                    'Invalid Field(s): '.$invalidFields->join(', ')
                );
                $this->fields = $fields;
            } else {
                $this->fields = $columns;
            }
            // Make each field a collection to hold the future versions
            $this->fields = $this->fields->mapWithKeys(fn ($item) => [$item => collect([$item])]);
        }

        return $this->fields;
    }

    /**
     * Given a list of attributes create a new version entry for each field.
     * Values are all stored as text, and null means the value had not changed.
     * Any missing attributes are assumed to have not changed.
     *
     * @param  array  $attributes
     * @return void
     */
    protected function addVersion(array $attributes): void
    {
        $attributes = collect($attributes);
        $attributes = $this->filterUntrackedAttributes($attributes);
        $attributes = $this->stringifyAttributes($attributes);
        $attributes = $this->filterUnchangedAttributes($attributes);
        if ($attributes->isNotEmpty()) {
            $this->getFields()->transform(function ($field, $key) use ($attributes) {
                if ($attributes->has($key)) {
                    $field[] = $attributes[$key];
                } else {
                    $field[] = null;
                }

                return $field;
            });
        }
    }

    protected function stringifyAttributes(Collection $attributes): Collection
    {
        return $attributes->transform(function ($item) {
            return var_export($item, true);
        });
    }

    /**
     * Given an array of attributes/values remove any which have not changed
     * since the last version. Also filters out any attributes we are not
     * monitoring.
     */
    protected function filterUnchangedAttributes(Collection $attributes): Collection
    {
        $fields = $this->getFields();

        return collect($attributes)->filter(function ($item, $key) use ($fields) {
            return $fields[$key]->filter()->last() !== $item;
        });
    }

    private function filterUntrackedAttributes(Collection $attributes): Collection
    {
        return $attributes->intersectByKeys($this->getFields());
    }

    protected function output()
    {
        $headers = $this->getFields()->first()->slice(1)->map(function ($item, $key) {
            return 'Change '.$key;
        });
        $headers->prepend('Field');
        $table = new Table($this->getSection());
        $table->setHeaderTitle(class_basename($this->model).' WatchModelCommand.php'.$this->model->getKey());
        $table->setHeaders($headers->toArray());
        foreach ($this->fields as $field) {
            $table->addRow($field->toArray());
        }
        $this->getSection()->clear();
        $table->render();
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
}

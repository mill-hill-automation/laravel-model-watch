<?php

namespace Mha\LaravelModelWatch;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

class ModelWatcher
{
    protected Model $model;

    protected ConsoleSectionOutput $section;

    protected Collection $fields;

    protected Collection $watchAttributes;

    public function __construct(Model $model, ConsoleSectionOutput $section)
    {
        $this->model = $model;
        $this->section = $section;
        $this->watchAttributes = collect($model->getAttributes())->keys();
    }

    public function watchAttributes(iterable $attributes): self
    {
        $this->watchAttributes = collect($attributes);

        return $this;
    }

    public function refresh(): self
    {
        if ($this->addVersion($this->model->refresh()->toArray())) {
            $this->output();
        }

        return $this;
    }

    /**
     * Ask the database for all the columns on the table for the watched model,
     * and filters them according to --field options if given.
     */
    protected function getFields(): Collection
    {
        if (! isset($this->fields)) {
            // Make each field a collection to hold the future versions
            $this->fields = $this->watchAttributes->mapWithKeys(fn ($item) => [$item => collect([$item])]);
        }

        return $this->fields;
    }

    /**
     * Given a list of attributes create a new version entry for each field.
     * Values are all stored as text, and null means the value had not changed.
     * Any missing attributes are assumed to have not changed.
     *
     * @param  array  $attributes
     * @return bool True if a new version was created, false if there were no changes to add
     */
    protected function addVersion(array $attributes): bool
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

            return true;
        }

        return false;
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
            return $key == 1 ? 'Initial' : 'Change '.$key - 1;
        });
        $headers->prepend('Field');
        $table = new Table($this->section);
        $id = is_array($this->model->getKey()) ? implode(', ', $this->model->getKey()) : $this->model->getKey();
        $table->setHeaderTitle(class_basename($this->model).' '.$id);
        $table->setHeaders($headers->toArray());
        foreach ($this->fields as $field) {
            $table->addRow($field->toArray());
        }
        $this->section->clear();
        $table->render();
    }

    public static function create(Model $model, ConsoleSectionOutput $section): self
    {
        return new self($model, $section);
    }
}

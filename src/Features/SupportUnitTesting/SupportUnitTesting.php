<?php

namespace Livewire\Features\SupportUnitTesting;

use function Synthetic\on;
use Livewire\Mechanisms\ComponentDataStore;
use Livewire\LivewireSynth;
use Livewire\Component;
use Illuminate\Validation\ValidationException;

class SupportUnitTesting
{
    function boot()
    {
        if (! app()->environment('testing')) return;

        on('dehydrate', function ($synth, $target, $context) {
            if (! $synth instanceof LivewireSynth) return;

            return function ($value) use ($context, $target) {
                ComponentDataStore::set($target, 'testing.html', $context->effects['html'] ?? null);
                ComponentDataStore::set($target, 'testing.view', null);

                return $value;
            };
        });

        on('render', function ($target, $view, $data) {
            return function () use ($target, $view) {
                ComponentDataStore::set($target, 'testing.view', $view);
            };
        });

        on('mount', function ($name, $params, $parent, $key, $slots, $hijack) {
            return function ($target) {
                return function ($html) use ($target) {
                    ComponentDataStore::set($target, 'testing.html', $html);
                };
            };
        });

        app('synthetic')->on('hydrate', function ($synth, $rawValue, $meta) {
            if (! $synth instanceof LivewireSynth) return;

            return function ($target) {
                ComponentDataStore::set($target, 'testing.validator', null);
            };
        });

        on('exception', function ($target, $e, $stopPropagation) {
            if (! $target instanceof Component) return;
            if (! $e instanceof ValidationException) return;

            ComponentDataStore::set($target, 'testing.validator', $e->validator);
        });
    }
}

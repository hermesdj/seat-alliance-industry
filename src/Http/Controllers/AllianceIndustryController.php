<?php

namespace RecursiveTree\Seat\AllianceIndustry\Http\Controllers;

use Illuminate\Http\Request;
use RecursiveTree\Seat\PricesCore\Models\PriceProviderInstance;


class AllianceIndustryController
{
    public function about()
    {
        return view("allianceindustry::about");
    }

    public function buildTimePriceProviderConfiguration(Request $request)
    {
        $existing = PriceProviderInstance::find($request->id);

        $id = $request->id;
        $name = $existing->name ?? $request->name ?? '';
        $reaction_multiplier = $existing->configuration['reactions'] ?? 1;
        $manufacturing_multiplier = $existing->configuration['manufacturing'] ?? 1;

        return view('allianceindustry::priceprovider.buildTimeConfiguration', compact('id', 'name', 'reaction_multiplier', 'manufacturing_multiplier'));
    }

    public function buildTimePriceProviderConfigurationPost(Request $request)
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'required|string',
            'manufacturing' => 'required|integer',
            'reactions' => 'required|integer',
        ]);

        $model = PriceProviderInstance::findOrNew($request->id);
        $model->name = $request->name;
        $model->backend = 'recursivetree/seat-alliance-industry/build-time';
        $model->configuration = [
            'reactions' => (int)$request->reactions,
            'manufacturing' => (int)$request->manufacturing,
        ];
        $model->save();

        return redirect()->route('pricescore::settings')->with('success', trans('allianceindustry::ai-common.price_provider_create_success'));
    }
}
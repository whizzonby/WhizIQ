<x-filament-panels::page>

    <x-filament.plans.all :is-grouped="true" preselected-interval="year" current-subscription-uuid="{{$subscriptionUuid}}" :calculate-saving-rates="true"/>

</x-filament-panels::page>

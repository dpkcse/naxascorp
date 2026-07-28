@props(['title' => 'Dashboard', 'breadcrumbs' => [['label' => 'Dashboard']]])
<x-layouts.app.sidebar :title="$title" :breadcrumbs="$breadcrumbs">{{ $slot }}</x-layouts.app.sidebar>

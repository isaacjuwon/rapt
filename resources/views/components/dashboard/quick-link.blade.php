@props(['href', 'icon', 'label'])

<a href="{{ $href }}"
   class="flex flex-col items-center justify-center p-4 text-center rounded-lg transition-colors duration-200 bg-gray-50 hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700">
    <flux:icon :name="$icon" class="!size-7 mb-2 text-gray-600 dark:text-gray-400" />
    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</span>
</a>

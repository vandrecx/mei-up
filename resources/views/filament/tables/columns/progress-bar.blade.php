<div class="flex items-center space-x-2">
    <div class="flex-1 bg-gray-200 rounded-full h-2 dark:bg-gray-700">
        <div 
            class="h-2 rounded-full transition-all duration-300 {{ 
                match($getState()['color']) {
                    'success' => 'bg-green-500',
                    'primary' => 'bg-blue-500', 
                    'warning' => 'bg-yellow-500',
                    'danger' => 'bg-red-500',
                    default => 'bg-gray-500'
                }
            }}" 
            style="width: {{ min(100, $getState()['progresso']) }}%">
        </div>
    </div>
    <span class="text-sm font-medium text-gray-900 dark:text-gray-100 min-w-[3rem]">
        {{ number_format($getState()['progresso'], 1) }}%
    </span>
</div>
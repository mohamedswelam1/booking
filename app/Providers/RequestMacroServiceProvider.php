<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class RequestMacroServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Request::macro('validateAndExecute', function (callable $action, array $rules = []) {
            if (!empty($rules)) {
                $this->validate($rules);
            }
            
            try {
                return $action($this->validated() ?? $this->all());
            } catch (\InvalidArgumentException $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ], 422);
            }
        });

        Request::macro('executeService', function (callable $serviceCall) {
            try {
                $result = $serviceCall();
                return response()->json([
                    'status' => 'success',
                    'data' => $result
                ]);
            } catch (\InvalidArgumentException $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ], 422);
            } catch (\Exception $e) {
                \Log::error('Service error: ' . $e->getMessage());
                return response()->json([
                    'status' => 'error',
                    'message' => 'An unexpected error occurred'
                ], 500);
            }
        });
    }
}

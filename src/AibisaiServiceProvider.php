<?php

namespace Jxgame\Aibisai;

use Illuminate\Support\ServiceProvider;

class AibisaiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
         // 单例绑定服务 
        $this->app->singleton('aibisai', function ($app) { 
            return new Aibisai($app['session'], $app['config']); 
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
      	//加载路由文件
		include __DIR__.'/routes/route.php';  
      
     //   $this->loadViewsFrom(__DIR__ . '/views', 'Aibisai'); // 视图目录指定 
        $this->publishes([ 
          //  __DIR__.'/views' => base_path('resources/views/vendor/aibisai'),  // 发布视图目录到resources 下 
            __DIR__.'/config/aibisai.php' => config_path('aibisai.php'), // 发布配置文件到 laravel 的config 下

        ]);
    }
}

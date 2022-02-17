<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use League\Flysystem\Filesystem;

class GenerateFilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public array $dataSet)
    {
    }

    public function handle():void
    {
        $namespace = $this->getNameSpace($this->dataSet['scope']);
        [$tableName, $className] = $this->className($this->dataSet['name']);
        $variables = $this->getStubVariables($namespace, $className, $tableName);
        $modelContent = $this->getSourceFile($variables);
        $path = $this->makeDirectory($namespace);
        $this->makeFileInGivenPath($path, $className, $modelContent);
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param  string  $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        $models = __DIR__ . '/../Models/';
        $path = $models . Str::of($path)->replace('\\', '/');
        if (! File::isDirectory($path)) {
            File::makeDirectory($path, 0777, true, true);
        }

        return $path;
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param  string  $path
     * @return bool
     */
    protected function makeFileInGivenPath(string $path, $className, $modelContent): bool
    {
        return File::put($path . '/' . $className . '.php', $modelContent);
    }

    /**
     * Return the stub file path
     * @return string
     *
     */
    public function getStubPath()
    {
        return base_path('app/Jobs/stubs') . '/model-tamplate.stub';
    }

    /**
     * Return the stub file path
     * @return array
     *
     */
    public function className($className): array
    {
        $className = $this->getSortedDirName($className);
        $tableName = Str::of($className)->snake();
        $className = (Str::endsWith($className, 's')) ? Str::replaceLast('s', '', $className) : $className;

        return [$tableName, $className];
    }

    /**
     * Return the stub file path
     * @return string
     *
     */
    public function getNameSpace($namespace): string
    {
        $dirName = collect($namespace)->map(function ($dirName) {
            return $this->getSortedDirName($dirName);
        })->toArray();

        return implode ("\\", $dirName);
    }

    private function getSortedDirName($dirName)
    {
        return ucfirst(Str::camel($dirName));
    }
    /**
     **
     * Map the stub variables present in stub to its value
     *
     * @return array
     *
     */
    public function getStubVariables($nameSpace, $className, $tableName)
    {
        return [
            '{namespace}'         => 'App\\Models\\' . $nameSpace,
            '{class_name}'        => $className,
            '{table_name}'        => $tableName,
        ];
    }


    /**
     * Get the stub path and the stub variables
     *
     * @return bool|mixed|string
     *
     */
    public function getSourceFile($stubVariables)
    {
        return $this->getStubContents($this->getStubPath(), $stubVariables);
    }


    /**
     * Replace the stub variables(key) with the desire value
     *
     * @param $stub
     * @param array $stubVariables
     * @return bool|mixed|string
     */
    public function getStubContents($stub , $stubVariables = [])
    {
        $contents = file_get_contents($stub);
        foreach ($stubVariables as $search => $replace)
        {
            $contents = str_replace($search , $replace, $contents);
        }

        return $contents;

    }
}

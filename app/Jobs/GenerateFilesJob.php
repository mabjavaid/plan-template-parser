<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

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

    protected function makeDirectory(string $path): string
    {
        $models = __DIR__ . '/../Models/';
        $path = $models . Str::of($path)->replace('\\', '/');
        if (! File::isDirectory($path)) {
            File::makeDirectory($path, 0777, true, true);
        }

        return $path;
    }

    protected function makeFileInGivenPath(string $path, $className, $modelContent): bool
    {
        return File::put($path . '/' . $className . '.php', $modelContent);
    }

    protected function getStubPath(): string
    {
        return base_path('app/Jobs/stubs') . '/model-tamplate.stub';
    }

    protected function className(string $className): array
    {
        $className = $this->getSortedDirName($className);
        $tableName = Str::of($className)->snake();
        $className = (Str::endsWith($className, 's')) ? Str::replaceLast('s', '', $className) : $className;

        return [$tableName, $className];
    }

    protected function getNameSpace(array $namespace): string
    {
        $dirName = collect($namespace)->map(function ($dirName) {
            return $this->getSortedDirName($dirName);
        })->toArray();

        return implode ("\\", $dirName);
    }

    protected function getSortedDirName(string $dirName): string
    {
        return ucfirst(Str::camel($dirName));
    }

    protected function getStubVariables(string $nameSpace, string $className, string $tableName): array
    {
        return [
            '{namespace}'         => 'App\\Models\\' . $nameSpace,
            '{class_name}'        => $className,
            '{table_name}'        => $tableName,
        ];
    }

    protected function getSourceFile(array $stubVariables): string
    {
        return $this->getStubContents($this->getStubPath(), $stubVariables);
    }

    protected function getStubContents($stub , $stubVariables = []): string
    {
        $contents = file_get_contents($stub);
        foreach ($stubVariables as $search => $replace)
        {
            $contents = str_replace($search , $replace, $contents);
        }

        return $contents;

    }
}

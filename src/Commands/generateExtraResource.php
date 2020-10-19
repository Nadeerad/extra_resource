<?php

namespace Nadeera\ExtraResource\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class generateExtraResource extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extra:resource {model_name} {table_name} {file_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Multiple Resource Files';

    protected $files;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
        parent::__construct();
    }

    /**
    * Function to get, data type to return based on field type 
    * @param    String  $fieldType  Column type
    * @return   String  returnType  Appropriate type to return from API
    */
    public function getReturnType($fieldType)
    {
        
        switch ($fieldType) {
            case "string":
                return "string";
                break;
            case "int":
                return "int";
                break;
            case "datetime":
                return "string";
                break;
            case "bigint":
                return "int";
                break;
            default:
                return "string";
          }
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Get model name
        $modelName          = $this->argument('model_name');
        // derive folder name of model from model name
        $modelFolderName    = Str::plural($modelName);
        
        // Get File Name
        $resourseFileName   = $this->argument('file_name');

        // Get Table name
        $tableName          = $this->argument('table_name');
        

        /////
        $file           = "${resourseFileName}.php";
        $path           = app_path();
        
        $file           = $path."/Http/Resources/$file";
        $resourcesDir   = $path."/Http/Resources";
       
        /////

        if ($resourseFileName === '' || is_null($resourseFileName) || empty($resourseFileName)) {
            return $this->error('Composer Name Invalid..!');
        }
        
        $table_column = Schema::getColumnListing($tableName);
        
        $return_str = "";
        foreach($table_column as $key => $column)
        {
            $dbType     = Schema::getColumnType($tableName, $column);
            $returnType = $this->getReturnType($dbType);
            $space      = "            ";
            $return_str .= $space.'"'.$column.'" => ('.$returnType.')$this->'.$column.','. PHP_EOL;
            
        }
        
        $contents       =
'
<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Entities\\'.$modelFolderName.'\\'.$modelName.';

class '.$resourseFileName.' extends JsonResource
{
    function __construct('.$modelName.' $model)
    {
        parent::__construct($model);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
'.$return_str.'
            
        ];
    }
}
?>';

        if($this->files->isDirectory($resourcesDir)){
            if($this->files->isFile($file)){
                return $this->error($resourseFileName.' File Already exists!');
            }

            if(!$this->files->put($file, $contents)){
                return $this->error('Something went wrong!');
            } else {
                $this->info("$resourseFileName generated!");
            }
        }
        else{
            $this->files->makeDirectory($resourcesDir, 0777, true, true);

            if(!$this->files->put($file, $contents)) {
                return $this->error('Something went wrong!');
            } else {
                $this->info("$resourseFileName generated!");
            }

        }

    }
}
<?php 

namespace App\Imports;

use Maatwebsite\Excel\Concerns\{Importable, WithHeadingRow, SkipsOnFailure, SkipsEmptyRows, SkipsOnError, SkipsFailures, SkipsErrors};

class ImportHandle implements WithHeadingRow, SkipsOnFailure, SkipsEmptyRows, SkipsOnError
{

    use Importable, SkipsFailures, SkipsErrors;

}

 ?>
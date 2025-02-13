<?php

declare(strict_types=1);

namespace DS\CbBuilder\Database;

use DS\fluidHelpers\Utility\SimpleDatabaseQuery;
use Exception;

final class DatabaseUtilityException extends Exception {}

/**
 * SELECT TABLE_NAME, COLUMN_NAME 
*FROM INFORMATION_SCHEMA.COLUMNS 
*WHERE COLUMN_NAME = 'user_id' 
*AND TABLE_SCHEMA = 'your_database_name';
 */

final class DatabaseUtility
{
    
}
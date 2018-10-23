<?php
declare(strict_types=1);

namespace MVQN\UCRM\Plugins;

/**
 * Class Log
 *
 * @package MVQN\UCRM\Plugins
 * @author Ryan Spaeth <rspaeth@mvqn.net>
 * @final
 */
final class Log
{
    // =================================================================================================================
    // CONSTANTS
    // -----------------------------------------------------------------------------------------------------------------

    /** @const int The options to be used when json_encode() is called. */
    private const DEFAULT_JSON_OPTIONS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

    /** @const string The format to be used by the logging functions as a timestamp. */
    private const TIMESTAMP_FORMAT_DATETIME = "Y-m-d H:i:s.u";

    /** @const string The format to be used by the logging functions as a timestamp for file names. */
    private const TIMESTAMP_FORMAT_DATEONLY = "Y-m-d";

    // =================================================================================================================
    // PATHS
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Provides the path to the current log file.
     *
     * @return string Returns the absolute path to the 'data/plugin.log' file.
     * @throws Exceptions\PluginNotInitializedException
     */
    private static function logFile(): string
    {
        $path = Plugin::getDataPath()."/plugin.log";

        return realpath($path) ?: $path;
    }

    /**
     * Provides the path to the rotated logs folder, or an exact file if a date/time is provided.
     *
     * @param \DateTime|null $logFile An optional date/time for which to find a matching rotated log file.
     * @return string Returns the absolute path to either the 'data/logs/' folder or the corresponding rotated log file.
     * @throws Exceptions\PluginNotInitializedException
     */
    private static function logsPath(\DateTime $logFile = null): string
    {
        $path = Plugin::getDataPath()."/logs/";

        if($logFile !== null)
            $path .= $logFile->format(self::TIMESTAMP_FORMAT_DATEONLY).".log";

        return realpath($path) ?: $path;
    }

    // =================================================================================================================
    // WRITING
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Writes a message to the current log file.
     *
     * @param string $message The message to be appended to the current log file.
     * @return string Returns the same message that was logged.
     * @throws Exceptions\PluginNotInitializedException
     */
    public static function write(string $message): string
    {
        // Get the current log file's path.
        $logFile = self::logFile();

        // If the log file's folder does NOT exist, THEN create it!
        if(!file_exists(dirname($logFile)))
            mkdir(dirname($logFile));

        // Generate the log line with prepended timestamp.
        $line = sprintf(
            "[%s] %s",
            (new \DateTime())->format(self::TIMESTAMP_FORMAT_DATETIME),
            $message
        );

        // Append the contents to the current log file, creating it as needed.
        file_put_contents(
            $logFile,
            $line."\n",
            FILE_APPEND | LOCK_EX
        );

        // Return the exact representation of the line!
        return $line;
    }

    /**
     * Writes a message to the current log file.
     *
     * @param array $array The array to write to the current log file.
     * @param int $options An optional set of valid JSON_OPTIONS that should be used when encoding the array.
     * @return string Returns the same message that was logged.
     * @throws Exceptions\PluginNotInitializedException
     */
    public static function writeArray(array $array, int $options = self::DEFAULT_JSON_OPTIONS): string
    {
        // JSON encode the array and then write it to the current log file.
        $text = json_encode($array, $options);
        return self::write($text);
    }

    /**
     * Writes a message to the current log file.
     *
     * @param \JsonSerializable $object The object (that implements JsonSerializable) to write to the current log file.
     * @param int $options An optional set of valid JSON_OPTIONS that should be used when encoding the array.
     * @return string Returns the same message that was logged.
     * @throws Exceptions\PluginNotInitializedException
     */
    public static function writeObject(\JsonSerializable $object, int $options = self::DEFAULT_JSON_OPTIONS): string
    {
        // JSON encode the object and then write it to the current log file.
        $text = json_encode($object, $options);
        return self::write($text);
    }

    // =================================================================================================================
    // VIEWING
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Reads the specified number of trailing lines from a starting position in this Plugin's log file.
     *
     * @param int $start The line number (zero-based) from which to start returning lines, defaults to the file start.
     * @param int $count The number of lines after $start of the file for which to return. 0 = All Lines (default)
     * @param bool $timestamped If TRUE, then return the timestamp as well, defaults to FALSE.
     * @return array|null Returns an array of the requested lines, associative if $timestamped = TRUE.
     * @throws Exceptions\PluginNotInitializedException
     * @throws Exceptions\RequiredFileNotFoundException
     */
    public static function lines(int $start = 0, int $count = 0, bool $timestamped = false): ?array
    {
        // Get the current log file's filename
        $logFile = self::logFile();

        // IF the file does NOT exist, THEN throw an Exception!
        if(!file_exists($logFile))
            throw new Exceptions\RequiredFileNotFoundException(
                "A plugin.log file could not be found at '".$logFile."'.");

        // Split the current log file's lines by any combination of line-breaks.
        $lines = preg_split("/[\r\n|\r|\n]+/", file_get_contents($logFile));

        // Remove the final empty line that always seems to be there...
        if($lines[count($lines) - 1] === "")
            unset($lines[count($lines) - 1]);

        // IF the specified count is 0, THEN make the count from the start to the end of the file.
        if($count === 0)
            $count = count($lines) - $start;

        // IF the specified count is less than 0, THEN set the start and count to reflect a negative read.
        if($count < 0)
        {
            $start += $count;
            $count = -$count;
        }

        // IF the specified start is less than 0 OR the start + count exceeds the amount of the lines, THEN return NULL!
        if($start < 0 || $start + $count > count($lines))
            return null;

        // Get only the lines from the requested start through the count.
        $lines = array_slice($lines, $start, $count);

        // Initialize an array to store the results.
        $linesArray = [];

        // Loop through each line...
        foreach($lines as $line)
        {
            // Trim off any extra white-space.
            $line = trim($line);

            // IF the line is empty, THEN skip it!
            if($line === "")
                continue;

            // Split the line into timestamp and the line's text.
            $parts = explode("] ", $line);

            // IF there is at least one part (which should be always) AND the first part starts with '['...
            if(count($parts) > 0 && strpos($parts[0], "[") === 0)
            {
                // THEN we found a timestamp...
                $timestamp = str_replace("[", "", array_shift($parts));

                // If a timestamp-indexed associative array was requested...
                if($timestamped)
                    // THEN return one!
                    $linesArray[$timestamp] = implode("] ", $parts);
                else
                    // OTHERWISE return an indexed array of just the line's text.
                    $linesArray[] = implode("] ", $parts);
            }
        }

        // Return the array!
        return $linesArray;
    }

    /**
     * Reads the specified number of trailing lines from this Plugin's log file.
     *
     * @param int $tail The number of lines from the end of the file for which to return. 0 = All Lines (default)
     * @param bool $timestamped If TRUE, then return the timestamp as well, defaults to FALSE.
     * @return array|null Returns an array of the requested lines, associative if $timestamped = TRUE.
     * @throws Exceptions\PluginNotInitializedException
     * @throws Exceptions\RequiredFileNotFoundException
     */
    public static function tail(int $tail = 0, bool $timestamped = false): ?array
    {
        return self::lines(0, -$tail, $timestamped);
    }

    /**
     * Returns the specific line number from this Plugin's log file.
     *
     * @param int $number The line number of which to return from the log file.
     * @param bool $timestamped If TRUE, then return the timestamp prefix as well, defaults to FALSE.
     * @return string|null Returns the string value of the specified line of the log, or NULL if not found!
     * @throws Exceptions\PluginNotInitializedException
     * @throws Exceptions\RequiredFileNotFoundException
     */
    public static function line(int $number, bool $timestamped = false): ?string
    {
        // IF timestamped is set...
        if($timestamped)
        {
            // THEN prepend the timestamp to the log line, as it would have appeared directly in the log.
            $parts = self::lines($number, 1, true);
            $timestamp = array_keys($parts)[0];
            $line = array_values($parts)[0];

            return "[$timestamp] $line";
        }
        else
            // OTHERWISE, simply return the log entry.
            return self::lines($number, 1)[0];
    }

    // =================================================================================================================
    // HELPERS
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Clears the current log file.
     */
    public static function clear(): void
    {
        $logFile = self::logFile();

        if(!file_exists(dirname($logFile)))
            mkdir(dirname($logFile));

        file_put_contents(
            $logFile,
            "",
            LOCK_EX
        );
    }

    /**
     * Provides a simple query to determine whether or not the current log file is empty.
     *
     * @return bool Returns TRUE if the current log file is empty, otherwise FALSE.
     * @throws Exceptions\PluginNotInitializedException
     * @throws Exceptions\RequiredFileNotFoundException
     */
    public static function isEmpty(): bool
    {
        $logFile = self::logFile();

        if(file_exists($logFile) && count(self::lines()) > 0)
            return false;

        return true;
    }

    // =================================================================================================================
    // SEARCHING
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Searches for log entries between the starting date/time (inclusive) and the ending date/time (exclusive).
     *
     * @param \DateTime $start A starting date/time for which to use when matching the earliest log entry.
     * @param \DateTime $end An ending date/time for which to use when matching the latest log entry.
     * @param bool $rotated If TRUE, the search will include rotated log files as well, defaults to FALSE.
     * @return array|null Returns a timestamp-indexed associative array of matching log entries.
     * @throws Exceptions\PluginNotInitializedException
     * @throws Exceptions\RequiredFileNotFoundException
     */
    public static function between(\DateTime $start, \DateTime $end, bool $rotated = false): ?array
    {
        // Initialize an empty timestamp-indexed array to store the matching log lines.
        $matching = [];

        // IF loading archived/rotated files for searching has been requested and a 'data/logs/' folder exists...
        if($rotated && file_exists(Plugin::getDataPath()."/logs/"))
        {
            // Set an inclusive starting date and and exclusive ending date based on the dates provided.
            $inclusiveStartDate = new \DateTime($start->format(self::TIMESTAMP_FORMAT_DATEONLY));
            $exclusiveEndDate = (new \DateTime($end->format(self::TIMESTAMP_FORMAT_DATEONLY)))
                ->add(new \DateInterval("P1D"));

            // Loop through each file and folder in the 'data/logs/' folder...
            foreach(scandir(self::logsPath()) as $filename)
            {
                // IF the current filename is a special file OR a directory/folder, THEN skip!
                if($filename === "." || $filename === ".." || is_dir($filename))
                    continue;

                // Generate the date/time associated with this file's name.
                $datetime = new \DateTime(str_replace(".log", "", $filename));

                // IF the filename's associated date/time is within the inclusive starting and exclusive ending dates...
                if($datetime >= $inclusiveStartDate && $datetime < $exclusiveEndDate)
                {
                    // THEN deserialize the log lines into their timestamp-indexed array equivalent.
                    $results = self::deserialize(file_get_contents(self::logsPath()."/$filename"),
                        function(string $timestamp, string $line) use ($start, $end)
                        {
                            $lineTimeStamp = new \DateTime($timestamp);
                            return ($lineTimeStamp >= $start && $lineTimeStamp < $end);
                        }
                    );

                    // AND then merge these de-serialized log lines with any existing matching log lines.
                    $matching = array_merge($matching, $results);
                }
            }
        }

        // IF the current log file is NOT empty...
        if(!self::isEmpty())
        {
            // THEN search through it for matching log entries as well!
            // NOTE: This section is necessary, in the cases where log rotation has not been used!

            // Generate a date/time from the earliest and latest log lines in the current log's entries.
            $logDateStart = new \DateTime(array_keys(Log::lines(0, 1, true))[0]);
            $logDateEnd = new \DateTime(array_keys(Log::tail(1, true))[0]);

            // IF the earliest and latest lines in the current log are within the end and start dates respectively...
            if($logDateStart < $end || $logDateEnd >= $start)
            {
                // THEN, Loop through the current log to determine which log lines should be included...
                foreach(self::lines(0, 0, true) as $timestamp => $line)
                {
                    // Generate a date/time for the current log entry.
                    $datetime = new \DateTime($timestamp);

                    // IF the current entry falls within the start and end date/times, THEN include it!
                    if($datetime >= $start && $datetime < $end)
                        $matching[$timestamp] = $line;
                }
            }
        }

        // Return all of the matching log entries!
        return $matching;
    }

    // =================================================================================================================
    // STORAGE
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Serialize a timestamp-indexed array of log lines into their textual equivalent.
     *
     * @param array $lines A set of timestamp-indexed log lines.
     * @param string $eol An optional end-of-line terminator to use, defaults to '\n'.
     * @return string|null
     */
    private static function serialize(array $lines, string $eol = "\n"): ?string
    {
        // Initialize an empty string builder to store the text lines for saving.
        $textLines = "";

        // IF the provided array is indexed and not an associative timestamp-indexed array, THEN return NULL!
        if(array_keys($lines) === range(0, count($lines) - 1))
            return null;

        // Loop through each of the current date's log lines, convert them to text and append them to the builder.
        foreach($lines as $timestamp => $line)
            $textLines .= "[$timestamp] $line$eol";

        // Return the textual log lines.
        return $textLines;
    }

    /**
     * Deserialize the textual log lines into their timestamp-indexed array equivalent.
     *
     * @param string $lines A string containing log lines from this Plugin's log file.
     * @param callable|null $inclusionHandler An optional handler to determine whether or not to include the log line.
     * @return array|null Returns an array of timestamp-indexed log lines, or NULL if none were found/matched.
     */
    private static function deserialize(string $lines, callable $inclusionHandler = null): ?array
    {
        // Split the provided textual log lines by any combination of line-breaks.
        $lines = preg_split("/[\r\n|\r|\n]+/", $lines);

        // Initialize a timestamp-indexed array of log lines.
        $parsed = [];

        // Loop through each line of the textual log lines...
        foreach($lines as $line)
        {
            // Trim away any extra whitespace.
            $line = trim($line);

            // IF the line is empty, THEN skip this line!
            if($line === "")
                continue;

            // Split the line into timestamp and actual log value.
            $parts = explode("] ", $line);

            // IF there is at least one part (which should be always) AND the first part starts with '['...
            if(count($parts) > 0 && strpos($parts[0], "[") === 0)
            {
                // THEN we found a timestamp...
                $timestamp = str_replace("[", "", array_shift($parts));

                // IF an inclusion handler was provided...
                if($inclusionHandler !== null)
                {
                    // THEN call the inclusion handler to determine inclusion of this line.
                    if($inclusionHandler($timestamp, implode("] ", $parts)))
                        $parsed[$timestamp] = implode("] ", $parts);
                }
                else
                {
                    // OTHERWISE, simply include this line.
                    $parsed[$timestamp] = implode("] ", $parts);
                }
            }
        }

        // Return either the timestamp-indexed array of log lines or NULL if none were found/matched!
        return count($parsed) > 0 ? $parsed : null;
    }

    /**
     * Saves the specified log lines to 'data/logs/<TIMESTAMP>.log', leaving the current day's logs intact.
     *
     * @param array $lines The timestamp-indexed log lines that should be saved.
     * @return int
     * @throws Exceptions\PluginNotInitializedException
     * @throws Exceptions\RequiredFileNotFoundException
     */
    private static function save(array $lines): int
    {
        // Initialize a file counter.
        $fileCount = 0;

        // IF the provided log lines are empty OR are not an associative array, THEN return 0 files affected!
        if(count($lines) === 0 || array_keys($lines) === range(0, count($lines) - 1))
            return $fileCount;

        // Generate dates based on the first log line and today's date.
        $logFirstDate = new \DateTime((new \DateTime(array_keys($lines)[0]))->format(self::TIMESTAMP_FORMAT_DATEONLY));
        $today = new \DateTime((new \DateTime())->format(self::TIMESTAMP_FORMAT_DATEONLY));

        // Set the saved logs location.
        //$directory = Plugin::getDataPath()."/logs/";

        // Create unreferenced copies of the first occurring log date, for use in the search loop.
        $currentDate = clone $logFirstDate;
        $currentNextDay = clone $logFirstDate;

        // Loop through each day from the starting date until today's date is reached...
        while($currentDate <= $today)
        {
            // Get all of the lines belonging to the current date.
            $currentLines = self::between($currentDate, $currentNextDay->add(new \DateInterval("P1D")));

            // Generate the file path according to the date.
            $filePath = self::logsPath($currentDate);

            // Load any existing log files for the current date.
            $loaded = ($currentDate != $today) ? self::load($currentDate) : [];

            // IF any pre-existing log files were found, merge them with the current date's log lines here.
            if($loaded)
                $currentLines = array_merge($currentLines, $loaded);

            // Serialize the log lines for writing to disk.
            $textLines = self::serialize($currentLines);

            // Save the textual logs to the corresponding day's log file or 'data/plugin.log' if they are from today.
            file_put_contents(($currentDate != $today) ? $filePath : self::logFile(), $textLines);

            // Increment the file counter of affected files.
            $fileCount++;

            // Increment the current date for the loop's work.
            $currentDate = $currentDate->add(new \DateInterval("P1D"));
        }

        // Return the total number of affected files.
        return $fileCount;
    }

    /**
     * Loads the log lines from the specified date.
     *
     * @param \DateTime $date The date for which to have log lines loaded.
     * @return array|null Returns an array of timestamp-indexed log lines, or NULL if none were found.
     * @throws Exceptions\PluginNotInitializedException
     */
    private static function load(\DateTime $date): ?array
    {
        // IF the date is from today...
        if($date->format(self::TIMESTAMP_FORMAT_DATEONLY) === (new \DateTime())->format(self::TIMESTAMP_FORMAT_DATEONLY))
            // THEN set the path to the 'data/plugin.log' file.
            $filePath = self::logFile();
        else
            // OTHERWISE set the path to the appropriate 'data/logs/<TIMESTAMP>.log' file.
            $filePath = self::logsPath($date);

        // IF the file exists, THEN deserialize it and return the timestamp-indexed array!
        if(file_exists($filePath))
            return self::deserialize(file_get_contents($filePath));

        // OTHERWISE, return NULL!
        return null;
    }

    /**
     * Archives the 'data/plugin.log' lines to 'data/logs/<TIMESTAMP>.log', leaving the current day's logs intact.
     *
     * @returns bool Returns TRUE if any files were created during the save, otherwise FALSE.
     * @throws Exceptions\PluginNotInitializedException
     * @throws Exceptions\RequiredFileNotFoundException
     */
    public static function rotate(): bool
    {
        if(Log::isEmpty())
            return false;

        return (self::save(Log::lines(0,0,true)) > 0);
    }

}

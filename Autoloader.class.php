<?php
/**
 * Autoloader is a class scanner with caching.
 * https://github.com/awbush/lightvc/blob/master/classes/Autoloader.class.php#L79
 */
class Autoloader{
	protected  static $classPaths=array();
	protected static $classFileSuffix='.class.php';
	protected static $cacheFilePath=null;
	protected static $cachedPaths=null;
	protected static $excludeFolderNames='/^CVS|\..*$/';//cvs directory and diretorys starting with a doc
	protected static $hasSaver=false;
	
	/*
	 * set the paths to search in when looking for a class
	 * @param array $path
	 * @return void
	 */
	public static function setClassPaths($paths)
	{
		self::$classPaths=$paths;
	}
	
	public static function addClassPath($paths)
	{
		self::$classPaths[]=$path;
	}
	/**
	 * set the full file path to the cache file to use
	 * Example:
	 * <code>
	 *  Autoloader::setCacheFilePath('/tmp/class_path_cache.txt');
	 *  </code>
	 *  @param string $path
	 *  @return void
	 */
	public static function setCacheFilePath($path)
	{
		self::$cacheFilePath=$path;
	}
	public static function setClassFileSuffix($suffix) {
		self::$classFileSuffix = $suffix;
	}
	public static function excludeFolderNamesMatchingRegex($regex) {
		self::$excludeFolderNames = $regex;
	}
	
	public static function loadClass($className)
	{
		$filePath=self::getCachedPath($className);
		if($filePath && file_exists($filePath))
		{
			//cached location is correct
			include($filePath);
			return true;
		}
		else
		{
			//scan for file
			foreach(self::$classPaths as $path)
			{
				if($filePath=self::searchForClassFile($className,$path))
				{
					self::$cachedPaths[$className]=$filePath;
					if(!self::$hasSaver)
					{
						register_shutdown_function(array('Autoloader','saveCachedPaths'));
						self::$hasSaver=true;
					}
					include($filePath);
					return true;
					
				}
			}
		}
		return false;
	}
	protected static function getCachedPath($className) {
		self::loadCachedPaths();
		if (isset(self::$cachedPaths[$className])) {
			return self::$cachedPaths[$className];
		} else {
			return false;
		}
	}
	
	protected static function loadCachedPaths() {
		if (is_null(self::$cachedPaths)) {
			if (self::$cacheFilePath && is_file(self::$cacheFilePath)) {
				self::$cachedPaths = unserialize(file_get_contents(self::$cacheFilePath));
			}
		}
	}
	/**
	 * write cached paths to disk
	 */
	public static function  saveCachedPaths() 
	{
		if (!file_exists(self::$cacheFilePath) || is_writable(self::$cacheFilePath)) {
			$fileContents = serialize(self::$cachedPaths);
		 
			file_put_contents(self::$cacheFilePath,"youku");
			$bytes = file_put_contents(self::$cacheFilePath, $fileContents);
			if ($bytes === false) {
				trigger_error('Autoloader could not write the cache file: ' . self::$cacheFilePath, E_USER_ERROR);
			}
		} else {
			trigger_error('Autoload cache file not writable: ' . self::$cacheFilePath, E_USER_ERROR);
		}
		
	}
	protected static function searchForClassFile($className, $directory) {
		if (is_dir($directory) && is_readable($directory)) {
			$d = dir($directory);
			while ($f = $d->read()) {
				$subPath = $directory . $f;
				if (is_dir($subPath)) {
					// Found a subdirectory
					if (!preg_match(self::$excludeFolderNames, $f)) {
						if ($filePath = self::searchForClassFile($className, $subPath . '/')) {
							return $filePath;
						}
					}
				} else {
					// Found a file
					if ($f == $className . self::$classFileSuffix) {
						return $subPath;
					}
				}
			}
		}
		return false;
	}
	
	
	
	
}
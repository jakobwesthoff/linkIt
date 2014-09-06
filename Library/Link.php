<?php
namespace Westhoffswelt\LinkIt;

/**
 * Representation of one link entry
 */
class Link
{
    protected $parent;

    protected $source;

    protected $target;

    public function __construct($data, LinkList $parent)
    {
        $this->parent = $parent;

        if (($this->source = $this->getRealSourcePath($data[0])) === false) {
            throw new Exception\InvalidLink(
                $parent->getLinkFile()->getRealPath(),
                $data[0],
                $data[1]
            );
        }
        $this->target = $this->getRealTargetPath($data[1]);
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function realize()
    {
        if (\file_exists($this->target)
            || !is_writable(dirname($this->target))
            || !symlink($this->source, $this->target)
        ) {
            throw new Exception\LinkRealization($this->source, $this->target);
        }
    }

    protected function getRealSourcePath($path)
    {
        // The source path is most likely relative to the basepath of the
        // linkList definition file
        $oldWorkingDirectory = getcwd();
        \chdir($this->parent->getBasePath()->getRealPath());
        $source = \realpath($path);
        \chdir($oldWorkingDirectory);
        return $source;
    }

    protected function getRealTargetPath($path)
    {
        /*
         * Determining the real target path is a little bit tricky, as it is
         * a file which does most likely not exist yet. Therefore realpath can not
         * be used.
         */

        // If the Path does not start with a / or ~ character it is a realative
        // path to the link file basepath
        if ($path[0] !== "/" && $path[0] !== "~") {
            $path = $this->parent->getBasePath()->getRealpath() . "/" . $path;
        }

        // Split path into segments to ease the processing
        $segments = $this->splitIntoPathSegments($path);

        // First path segment could be ~-Character, which indicates the home
        // directory of the current user
        if ($segments[0] === "~") {
            \array_splice(
                $segments,
                0,
                1,
                $this->splitIntoPathSegments(
                    \getenv("HOME")
                )
            );
        }

        // Handling relative path elements (.. and .) is neccessary
        return "/" . \implode("/", $this->canonicalizePathSegments($segments));
    }

    protected function canonicalizePathSegments($segments)
    {
        return \array_reduce(
            $segments,
            function ($reduced, $current) {
                $reduced = ($reduced === null)
                    ? array()
                    : $reduced;

                switch ($current) {
                    case ".":
                        // Just ignore . references
                        break;
                    case "..":
                        array_pop($reduced);
                        break;
                    default:
                        array_push($reduced, $current);
                }

                return $reduced;
            }
        );
    }

    protected function splitIntoPathSegments($path)
    {
        // Split the path into segments removing empty parts (eg. foo///bar)
        return \array_values(
            \array_filter(
                \explode("/", $path),
                'strlen'
            )
        );
    }
}

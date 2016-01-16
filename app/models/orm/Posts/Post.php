<?php

namespace Fitak;

use DateTime;
use Nette\Utils\Strings;
use Nextras\Orm\Entity\Entity;
use Nextras\Orm\Relationships\OneHasMany;
use Nextras\Orm\Relationships\ManyHasMany;
use Tags;


/**
 * @property string|NULL       $fbId
 * @property Post|NULL         $parent   {m:1 PostsRepository $comments}
 * @property Group|NULL        $group    {m:1 GroupsRepository $posts}
 * @property string            $message
 * @property DateTime          $createdTime
 * @property DateTime          $updatedTime
 * @property int|NULL          $commentsCount
 * @property string            $type     {enum self::TYPE_*}
 * @property string|NULL       $link
 * @property string|NULL       $name
 * @property string|NULL       $caption
 * @property string|NULL       $description
 * @property string|NULL       $picture
 * @property string|NULL       $source
 * @property User              $user {m:1 UsersRepository $posts}
 *
 * @property ManyHasMany|Tag[] $tags     {m:n TagsRepository primary}
 * @property OneHasMany|Post[] $comments {1:m PostsRepository $parent}
 * @property OneHasMany|Vote[] $votes {1:m VotesRepository $data}
 */
class Post extends Entity
{

	const TYPE_STATUS = 'status';
	const TYPE_VIDEO = 'video';
	const TYPE_LINK = 'link';
	const TYPE_PHOTO = 'photo';
	const TYPE_EVENT = 'event';

	/**
	 * @var Tags
	 * @inject
	 */
	public $tagParser;

	/**
	 * @return string[][] [string[] $cleanTags, string[] $originalTags]
	 */
	public function getParsedTags()
	{
		if ($this->isComment())
		{
			return [[], []];
		}

		return $this->tagParser->extractTags($this->message);
	}

	/**
	 * @return string
	 */
	public function getMessageWithoutTags()
	{
		if ($this->isComment())
		{
			return $this->message;
		}

		return Strings::trim($this->tagParser->separateMessage($this->message)[1]);
	}

	/**
	 * @return bool
	 */
	public function isComment()
	{
		return (bool) $this->parent;
	}

}

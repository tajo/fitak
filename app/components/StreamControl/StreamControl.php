<?php

use Fitak\Orm;
use Fitak\Post;
use Fitak\TemplateFactory;
use Fitak\User;
use Fitak\Vote;
use Kdyby\Facebook\Facebook;
use Nette\Application\UI;
use Nextras\Dbal\UniqueConstraintViolationException;

class StreamControl extends UI\Control
{

	/** @var IStreamDataSource */
	public $dataSource;

	/** @var int maximum number of topics on a single page */
	public $topicsPerPage = 20;
	private $templateFactory;
	public $orm;
	public $user;
	private $facebook;

	public function __construct(TemplateFactory $templateFactory, Orm $orm, User $user, Facebook $facebook)
	{
		parent::__construct();
		$this->templateFactory = $templateFactory;
		$this->orm = $orm;
		$this->user = $user;
		$this->facebook = $facebook;
	}


	public function render()
	{
		/** @var $paginator Nette\Utils\Paginator */
		$paginator = $this['paginator']->paginator;

		$this->template->topics = $this->dataSource->getTopics($paginator->itemsPerPage, $paginator->offset);
		$this->template->render();
	}

	public function handleVote($postId)
	{
		$post = $this->orm->posts->getById($postId);

		$vote = new Vote();
		$vote->data = $post;
		$vote->user = $this->user;
		$vote->isDownvote = 0;
		try {
			$this->orm->votes->persistAndFlush($vote);
		} catch (UniqueConstraintViolationException $e)
		{
			$this->flashMessage($e->getMessage());
		}
	}


	protected function createComponentPaginator()
	{
		$vp = new VisualPaginator();
		$vp->paginator->itemsPerPage = $this->topicsPerPage;
		$vp->paginator->itemCount = $this->dataSource->getTotalCount();

		return $vp;
	}

	protected function createComponentCommentForm()
	{

		$form = new CommentForm($this->orm, $this->user, $this->facebook);
		$form->onSuccess[] = callback($form, 'submitted');

		return $form;
	}

	protected function createTemplate($class = NULL)
	{
		$template = $this->templateFactory->createTemplate($this);
		$template->setFile(__DIR__ . '/StreamControl.latte');

		return $template;
	}

}

<?php

/*
 * This file is part of Phraseanet
 *
 * (c) 2005-2010 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhraseaFixture\UsrLists;
/**
 * 
 * @package
 * @license     http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link        www.phraseanet.com
 */
abstract class ListAbstract extends \Doctrine\Common\DataFixtures\AbstractFixture
{
  
  protected $user;
  
  protected $list;
  
  public function getUser()
  {
    return $this->user;
  }

  public function setUser(\User_Adapter $user)
  {
    $this->user = $user;
  }

  public function getList()
  {
    return $this->list;
  }

  public function setList(\Entities\UsrList $list)
  {
    $this->list = $list;
  }

}

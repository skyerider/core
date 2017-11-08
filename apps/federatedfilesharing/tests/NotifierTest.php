<?php
/**
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 *
 * @copyright Copyright (c) 2017, ownCloud GmbH
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */


namespace OCA\FederatedFileSharing\Tests;


use OCA\FederatedFileSharing\Notifier;
use OCP\IL10N;
use OCP\L10N\IFactory;
use OCP\Notification\INotification;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class NotifierTest
 *
 * @group  DB
 * @package OCA\FederatedFileSharing\Tests
 */

class NotifierTest extends \Test\TestCase {

	/** @var  IFactory | \PHPUnit_Framework_MockObject_MockObject */
	private $factory;

	public function setUp() {
		parent::setUp();

		$this->factory = $this->createMock(IFactory::class);
	}

	public function testPrepare() {
		$languageCode = 'en';
		$notification = $this->createMock(INotification::class);
		$il10 = $this->createMock(IL10N::class);
		$this->factory->method('get')
			->with('files_sharing', $languageCode)
			->will($this->returnValue($il10));

		$notifier = new Notifier($this->factory, \OC::$server->getEventDispatcher());

		$notification->method('getApp')
			->will($this->returnValue('files_sharing'));

		$notification->method('getSubject')
			->will($this->returnValue('remote_share'));

		$params = ['admin@http://localhost', 'admin@http://localhost', 'fed'];
		$notification->method('getSubjectParameters')
			->will($this->returnValue($params));

		$notification->expects($this->any())
			->method('setParsedSubject')
			->willReturn($notification);

		$notification->method('getActions')
			->will($this->returnValue([]));

		$called = array();
		\OC::$server->getEventDispatcher()->addListener('\OCA\FederatedFileSharing::local_shareReceived', function ($event) use (&$called) {
			$called[] = '\OCA\FederatedFileSharing::local_shareReceived';
			array_push($called, $event);
		});
		$this->assertEquals($notification, $notifier->prepare($notification, $languageCode));

		$this->assertTrue($called[1] instanceof GenericEvent);
	}
}

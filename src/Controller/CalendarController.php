<?php declare(strict_types=1);

namespace Bone\Calendar\Controller;

use Bone\Calendar\Collection\CalendarCollection;
use Bone\Calendar\Entity\Calendar;
use Bone\Calendar\Form\CalendarForm;
use Bone\Calendar\Service\CalendarService;
use Bone\Controller\Controller;
use Bone\Exception;
use Bone\Http\Response\LayoutResponse;
use Bone\View\Helper\AlertBox;
use Bone\View\Helper\Paginator;
use DateTime;
use Del\Form\Field\Submit;
use Del\Form\Form;
use Del\Icon;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CalendarController extends Controller
{
    /** @var int $numPerPage */
    private $numPerPage = 10;

    /** @var Paginator $paginator */
    private $paginator;

    /** @var CalendarService $service */
    private $service;

    /** @var string $layout */
    private $layout = 'layouts::admin';

    /**
     * @param CalendarService $service
     */
    public function __construct(CalendarService $service)
    {
        $this->paginator = new Paginator();
        $this->service = $service;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface $response
     * @throws \Exception
     */
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $db = $this->service->getRepository();
        $total = $db->getTotalCalendarCount();
        $this->paginator->setUrl('calendar?page=:page');
        $params = $request->getQueryParams();
        $page = array_key_exists('page', $params) ?(int) $params['page'] : 1;
        $this->paginator->setCurrentPage($page);
        $this->paginator->setPageCountByTotalRecords($total, $this->numPerPage);
        $calendars = new CalendarCollection($db->findBy([], null, $this->numPerPage, ($page *  $this->numPerPage) - $this->numPerPage));

        $body = $this->view->render('calendar::index', [
            'calendars' => $calendars,
            'paginator' => $this->paginator->render(),
        ]);

        return new LayoutResponse($body, $this->layout);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface $response
     * @throws \Exception
     */
    public function view(ServerRequestInterface $request): ResponseInterface
    {
        $db = $this->service->getRepository();
        $id = $request->getAttribute('id');
        $calendar = $db->find($id);
        $body = $this->view->render('calendar::view', [
            'calendar' => $calendar,
        ]);

        return new LayoutResponse($body, $this->layout);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface $response
     * @throws \Exception
     */
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        $msg = '';
        $form = new CalendarForm('createCalendar');

        if ($request->getMethod() === 'POST') {
            $post = $request->getParsedBody();
            $form->populate($post);

            if ($form->isValid()) {
                $data = $form->getValues();
                $this->setTimeZone($data);
                $calendar = $this->service->createFromArray($data);

                try {
                    $this->service->saveCalendar($calendar);
                    $msg = $this->alertBox(Icon::CHECK_CIRCLE . ' New event added to database.', 'success');
                    $form = new CalendarForm('createCalendar');
                } catch (Exception $e) {
                    $msg = $this->alertBox(Icon::WARNING . ' ' . $e->getMessage(), 'danger');
                }

            } else {
                $msg = $this->alertBox(Icon::REMOVE . ' There was a problem with the form.', 'danger');
            }
        }

        $form->getField('submit')->setValue('Create');
        $form = $form->render();
        $body = $this->view->render('calendar::create', [
            'form' => $form,
            'msg' => $msg,
        ]);

        return new LayoutResponse($body, $this->layout);
    }

    /**
     * @param array $data
     */
    private function setTimeZone(array $data): void
    {
        if (isset($data['timezoneOffset'])) {
            $dateFormat = $data['dateFormat'] ?? 'd/m/Y H:i';
            $start = new DateTime('now', new \DateTimeZone('Europe/London'));
            $timeZone = \timezone_name_from_abbr('', (int) $data['timezoneOffset'], (int) $start->format('I' ));
            $this->service->setTimeZone($timeZone);
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface $response
     * @throws \Exception
     */
    public function edit(ServerRequestInterface $request): ResponseInterface
    {
        $msg = '';
        $form = new CalendarForm('editCalendar');
        $id = $request->getAttribute('id');
        $db = $this->service->getRepository();
        /** @var Calendar $calendar */
        $calendar = $db->find($id);
        $form->populate($calendar->toArray());
        $form->getField('submit')->setValue('Update');

        if ($request->getMethod() === 'POST') {
            $post = $request->getParsedBody();
            $form->populate($post);
            if ($form->isValid()) {
                $data = $form->getValues();
                $calendar = $this->service->updateFromArray($calendar, $data);
                try {
                    $this->service->saveCalendar($calendar);
                    $msg = $this->alertBox(Icon::CHECK_CIRCLE . ' Event details updated.', 'success');
                    $form = new CalendarForm('createCalendar');
                } catch (Exception $e) {
                    $msg = $this->alertBox(Icon::WARNING . ' ' . $e->getMessage(), 'danger');
                }
            } else {
                $msg = $this->alertBox(Icon::REMOVE . ' There was a problem with the form.', 'danger');
            }
        }

        $form = $form->render();
        $body = $this->view->render('calendar::edit', [
            'form' => $form,
            'msg' => $msg,
        ]);

        return new LayoutResponse($body, $this->layout);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface $response
     * @throws \Exception
     */
    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttribute('id');
        $db = $this->service->getRepository();
        $form = new Form('deleteCalendar');
        $submit = new Submit('submit');
        $submit->setValue('Delete');
        $submit->setClass('btn btn-lg btn-danger');
        $form->addField($submit);
        /** @var Calendar $calendar */
        $calendar = $db->find($id);

        if ($request->getMethod() === 'POST') {
            $this->service->deleteCalendar($calendar);
            $msg = $this->alertBox(Icon::CHECK_CIRCLE . ' Calendar deleted.', 'warning');
            $form = '<a href="/admin/calendar" class="btn btn-lg btn-default">Back</a>';
            $text = '<p class="lead">The record has been deleted from the database.</p>';
        } else {
            $form = $form->render();
            $msg = $this->alertBox(Icon::WARNING . ' Warning, please confirm your intention to delete.', 'danger');
            $text = '<p class="lead">Are you sure you want to delete ' . $calendar->getEvent() . '?</p>';
        }

        $body = $this->view->render('calendar::delete', [
            'calendar' => $calendar,
            'form' => $form,
            'msg' => $msg,
            'text' => $text,
        ]);

        return new LayoutResponse($body, $this->layout);
    }

    /**
     * @param string $message
     * @param string $class
     * @return string
     */
    private function alertBox(string $message, string $class): string
    {
        $helper = new AlertBox();

        return $helper->alertBox([
            'message' => $message,
            'class' => $class,
        ]);
    }



    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface $response
     * @throws \Exception
     */
    public function calendarView(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute('user');
        $body = $this->view->render('calendar::calendar-with-draggable', [
            'user' => $user,
        ]);

        return new LayoutResponse($body, $this->layout);
    }
}

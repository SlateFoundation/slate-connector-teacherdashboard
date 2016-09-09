<?php

namespace Slate\Connectors\TeacherDashboard;

use DB;
use SpreadsheetWriter;

use Slate\Term;
use Slate\Courses\Section;


class Connector extends \Emergence\Connectors\AbstractConnector
{
    public static $connectorId = 'teacher-dashboard';

    public static function handleRequest($action = null)
    {
        switch ($action ?: $action = static::shiftPath()) {
            case 'students.csv':
                return static::handleStudentsRequest();
            case 'classes.csv':
                return static::handleClassesRequest();
            default:
                return parent::handleRequest($action);
        }
    }

    public static function handleStudentsRequest()
    {
        $GLOBALS['Session']->requireAccountLevel('Administrator');

        // get term
        if (!empty($_REQUEST['term'])) {
            if (!$Term = Term::getByHandle($_REQUEST['term'])) {
                return static::throwInvalidRequestError('term not found');
            }
        } else {
            $Term = Term::getClosest()->getMaster();
        }

        // init spreadsheet writer
        $spreadsheet = new SpreadsheetWriter();

        // write header
        $spreadsheet->writeRow([
            'Email',
            'Class'
        ]);

        // retrieve results
        $result = DB::query(
            'SELECT stu.Username, s.Code'
            .' FROM course_sections s'
            .' RIGHT JOIN course_section_participants p ON p.CourseSectionID = s.ID'
            .' JOIN people stu ON stu.ID = p.PersonID'
            .' WHERE s.TermID IN (%s) AND p.Role = "Student"'
            .' ORDER BY stu.Username, s.Code',
            [
                implode(',', $Term->getRelatedTermIDs())
            ]
        );

        // output results
        while ($row = $result->fetch_assoc()) {
            // write row
            $spreadsheet->writeRow($row);
        }

        // finish output
        $spreadsheet->close();
    }

    public static function handleClassesRequest()
    {
        $GLOBALS['Session']->requireAccountLevel('Administrator');

        // get term
        if (!empty($_REQUEST['term'])) {
            if (!$Term = Term::getByHandle($_REQUEST['term'])) {
                return static::throwInvalidRequestError('term not found');
            }
        } else {
            $Term = Term::getClosest()->getMaster();
        }

        // init spreadsheet writer
        $spreadsheet = new SpreadsheetWriter();

        // write header
        $spreadsheet->writeRow([
            'Mailbox',
            'Name',
            'Description',
            'Teachers',
            'Subject Folders',
            'Class Calendar'
        ]);

        // retrieve results
        $sections = Section::getAllByWhere(
            ['TermID IN ('.implode(',',$Term->getRelatedTermIDs()).')'],
            ['order' => 'Code']
        );

        // output results
        foreach ($sections AS $Section) {
            // write row
            $spreadsheet->writeRow([
                $Section->Code,
                $Section->Title . ' (' . $Section->Term->Title . ')',
                $Section->Title,
                implode(',', array_map(function ($Teacher) { return $Teacher->Username; }, $Section->Teachers)),
                $Section->Course->Title,
                ''
            ]);
        }

        // finish output
        $spreadsheet->close();
    }
}
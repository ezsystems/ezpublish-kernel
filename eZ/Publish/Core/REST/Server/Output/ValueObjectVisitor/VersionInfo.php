<?php
/**
 * File containing the VersionInfo ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor,
    eZ\Publish\Core\REST\Common\Output\Generator,
    eZ\Publish\Core\REST\Common\Output\Visitor,

    eZ\Publish\API\Repository\Values;

/**
 * VersionInfo value object visitor
 */
class VersionInfo extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $versionInfo = $data;

        $generator->startHashElement( 'VersionInfo' );

        $generator->startValueElement( 'id', $versionInfo->id );
        $generator->endValueElement( 'id' );

        $generator->startValueElement( 'versionNo', $versionInfo->versionNo );
        $generator->endValueElement( 'versionNo' );

        $generator->startValueElement(
            'status',
            $this->getStatusString( $versionInfo->status )
        );
        $generator->endValueElement( 'status' );

        $generator->startValueElement(
            'modificationDate',
            $versionInfo->modificationDate->format( 'c' )
        );
        $generator->endValueElement( 'modificationDate' );

        $generator->startObjectElement( 'Creator', 'User' );
        $generator->startAttribute(
            'href', $this->urlHandler->generate( 'user', array( 'user' => $versionInfo->creatorId ) )
        );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'Creator' );

        $generator->startValueElement(
            'creationDate',
            $versionInfo->creationDate->format( 'c' )
        );
        $generator->endValueElement( 'creationDate' );

        $generator->startValueElement(
            'initialLanguageCode',
            $versionInfo->initialLanguageCode
        );
        $generator->endValueElement( 'initialLanguageCode' );

        $generator->startValueElement(
            'languageCodes',
            implode( ',', $versionInfo->languageCodes )
        );
        $generator->endValueElement( 'languageCodes' );

        $this->visitNamesList( $generator, $versionInfo->names );

        $generator->startObjectElement( 'Content', 'ContentInfo' );
        $generator->startAttribute(
            'href',
            $this->urlHandler->generate( 'object', array( 'object' => $versionInfo->getContentInfo()->id ) )
        );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'Content' );

        $generator->endHashElement( 'VersionInfo' );
    }

    /**
     * Maps the given version $status to a representative string
     *
     * @param int $status
     * @return string
     */
    protected function getStatusString( $status )
    {
        switch ( $status )
        {
            case Values\Content\VersionInfo::STATUS_DRAFT:
                return 'DRAFT';

            case Values\Content\VersionInfo::STATUS_PUBLISHED:
                return 'PUBLISHED';

            case Values\Content\VersionInfo::STATUS_ARCHIVED:
                return 'ARCHIVED';
        }

        // FIXME: What exception to use?
        throw new \Exception( 'Undefined version status: ' . $status );
    }
}

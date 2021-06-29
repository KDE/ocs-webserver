<?php
/**
 *   ocs-webserver
 *
 *   Copyright 2016 by pling GmbH.
 *
 *     This file is part of ocs-webserver.
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Application\Model\Entity;

use DomainException;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;

class HiveContent implements InputFilterAwareInterface
{
    // attributes
    public $id;
    public $name;
    public $user;
    public $userdb;
    public $created;
    public $changed;
    public $deletedby;
    public $deletedat;
    public $type;
    public $language;
    public $depend;
    public $depend2;
    public $downloads;
    public $scoregood;
    public $scorebad;
    public $scorelastip1;
    public $scorelastip2;
    public $scorelastip3;
    public $scorelastip4;
    public $scorelastip5;
    public $scorelastip6;
    public $scorelastip7;
    public $scorelastip8;
    public $scorelastip9;
    public $scorelasttime;
    public $scorelastagent;
    public $scorelastsession1;
    public $scorelastsession2;
    public $scorelastsession3;
    public $description;
    public $category;
    public $summary;
    public $feedbackurl;
    public $answer;
    public $answerstatus;
    public $version;
    public $changelog;
    public $license;
    public $licensetype;
    public $status;
    public $approved;
    public $preview1;
    public $preview2;
    public $preview3;
    public $downloadname1;
    public $downloadtyp1;
    public $download1;
    public $downloadlink1;
    public $downloadfiletype1;
    public $downloadbuy1;
    public $downloadbuyreason1;
    public $downloadbuyprice1;
    public $downloadrepository1;
    public $downloadpackagename1;
    public $downloadgpgfingerprint1;
    public $downloadgpgsignature1;
    public $downloadname2;
    public $downloadversion2;
    public $downloadlink2;
    public $downloadfiletype2;
    public $downloadbuy2;
    public $downloadbuyreason2;
    public $downloadbuyprice2;
    public $downloadrepository2;
    public $downloadpackagename2;
    public $downloadgpgfingerprint2;
    public $downloadgpgsignature2;
    public $downloadname3;
    public $downloadversion3;
    public $downloadlink3;
    public $downloadfiletype3;
    public $downloadbuy3;
    public $downloadbuyreason3;
    public $downloadbuyprice3;
    public $downloadrepository3;
    public $downloadpackagename3;
    public $downloadgpgfingerprint3;
    public $downloadgpgsignature3;
    public $downloadname4;
    public $downloadversion4;
    public $downloadlink4;
    public $downloadfiletype4;
    public $downloadbuy4;
    public $downloadbuyreason4;
    public $downloadbuyprice4;
    public $downloadrepository4;
    public $downloadpackagename4;
    public $downloadgpgfingerprint4;
    public $downloadgpgsignature4;
    public $downloadname5;
    public $downloadversion5;
    public $downloadlink5;
    public $downloadfiletype5;
    public $downloadbuy5;
    public $downloadbuyreason5;
    public $downloadbuyprice5;
    public $downloadrepository5;
    public $downloadpackagename5;
    public $downloadgpgfingerprint5;
    public $downloadgpgsignature5;
    public $downloadname6;
    public $downloadversion6;
    public $downloadlink6;
    public $downloadfiletype6;
    public $downloadbuy6;
    public $downloadbuyreason6;
    public $downloadbuyprice6;
    public $downloadrepository6;
    public $downloadpackagename6;
    public $downloadgpgfingerprint6;
    public $downloadgpgsignature6;
    public $downloadlink7;
    public $downloadname7;
    public $downloadversion7;
    public $downloadfiletype7;
    public $downloadbuy7;
    public $downloadbuyreason7;
    public $downloadbuyprice7;
    public $downloadrepository7;
    public $downloadpackagename7;
    public $downloadgpgfingerprint7;
    public $downloadgpgsignature7;
    public $downloadlink8;
    public $downloadname8;
    public $downloadversion8;
    public $downloadfiletype8;
    public $downloadbuy8;
    public $downloadbuyreason8;
    public $downloadbuyprice8;
    public $downloadrepository8;
    public $downloadpackagename8;
    public $downloadgpgfingerprint8;
    public $downloadgpgsignature8;
    public $downloadlink9;
    public $downloadname9;
    public $downloadversion9;
    public $downloadfiletype9;
    public $downloadbuy9;
    public $downloadbuyreason9;
    public $downloadbuyprice9;
    public $downloadrepository9;
    public $downloadpackagename9;
    public $downloadgpgfingerprint9;
    public $downloadgpgsignature9;
    public $downloadlink10;
    public $downloadname10;
    public $downloadversion10;
    public $downloadfiletype10;
    public $downloadbuy10;
    public $downloadbuyreason10;
    public $downloadbuyprice10;
    public $downloadrepository10;
    public $downloadpackagename10;
    public $downloadgpgfingerprint10;
    public $downloadgpgsignature10;
    public $downloadlink11;
    public $downloadname11;
    public $downloadversion11;
    public $downloadfiletype11;
    public $downloadbuy11;
    public $downloadbuyreason11;
    public $downloadbuyprice11;
    public $downloadrepository11;
    public $downloadpackagename11;
    public $downloadgpgfingerprint11;
    public $downloadgpgsignature12;
    public $downloadgpgsignature11;
    public $downloadlink12;
    public $downloadname12;
    public $downloadversion12;
    public $downloadfiletype12;
    public $downloadbuy12;
    public $downloadbuyreason12;
    public $downloadbuyprice12;
    public $downloadrepository12;
    public $downloadpackagename12;
    public $downloadgpgfingerprint12;
    public $osbs_project;
    public $osbs_package;
    public $scoresum;
    public $scorecount;
    public $homepage1;
    public $homepagetype1;
    public $homepage2;
    public $homepagetype2;
    public $homepage3;
    public $homepagetype3;
    public $homepage4;
    public $homepagetype4;
    public $homepage5;
    public $homepagetype5;
    public $homepage6;
    public $homepagetype6;
    public $homepage7;
    public $homepagetype7;
    public $homepage8;
    public $homepagetype8;
    public $homepage9;
    public $homepagetype9;
    public $homepage10;
    public $homepagetype10;
    public $commentscount;
    public $fancount;
    public $knowledgebasecount;
    public $tags;
    public $donation;
    public $donationreason;
    public $gnomefiles;
    public $ean;
    public $is_imported;
    public $import_error;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->name = !empty($data['name']) ? $data['name'] : null;
        $this->user = !empty($data['user']) ? $data['user'] : null;
        $this->userdb = !empty($data['userdb']) ? $data['userdb'] : null;
        $this->created = !empty($data['created']) ? $data['created'] : null;
        $this->changed = !empty($data['changed']) ? $data['changed'] : null;
        $this->deletedby = !empty($data['deletedby']) ? $data['deletedby'] : null;
        $this->deletedat = !empty($data['deletedat']) ? $data['deletedat'] : null;
        $this->type = !empty($data['type']) ? $data['type'] : null;
        $this->language = !empty($data['language']) ? $data['language'] : null;
        $this->depend = !empty($data['depend']) ? $data['depend'] : null;
        $this->depend2 = !empty($data['depend2']) ? $data['depend2'] : null;
        $this->downloads = !empty($data['downloads']) ? $data['downloads'] : null;
        $this->scoregood = !empty($data['scoregood']) ? $data['scoregood'] : null;
        $this->scorebad = !empty($data['scorebad']) ? $data['scorebad'] : null;
        $this->scorelastip1 = !empty($data['scorelastip1']) ? $data['scorelastip1'] : null;
        $this->scorelastip2 = !empty($data['scorelastip2']) ? $data['scorelastip2'] : null;
        $this->scorelastip3 = !empty($data['scorelastip3']) ? $data['scorelastip3'] : null;
        $this->scorelastip4 = !empty($data['scorelastip4']) ? $data['scorelastip4'] : null;
        $this->scorelastip5 = !empty($data['scorelastip5']) ? $data['scorelastip5'] : null;
        $this->scorelastip6 = !empty($data['scorelastip6']) ? $data['scorelastip6'] : null;
        $this->scorelastip7 = !empty($data['scorelastip7']) ? $data['scorelastip7'] : null;
        $this->scorelastip8 = !empty($data['scorelastip8']) ? $data['scorelastip8'] : null;
        $this->scorelastip9 = !empty($data['scorelastip9']) ? $data['scorelastip9'] : null;
        $this->scorelasttime = !empty($data['scorelasttime']) ? $data['scorelasttime'] : null;
        $this->scorelastagent = !empty($data['scorelastagent']) ? $data['scorelastagent'] : null;
        $this->scorelastsession1 = !empty($data['scorelastsession1']) ? $data['scorelastsession1'] : null;
        $this->scorelastsession2 = !empty($data['scorelastsession2']) ? $data['scorelastsession2'] : null;
        $this->scorelastsession3 = !empty($data['scorelastsession3']) ? $data['scorelastsession3'] : null;
        $this->description = !empty($data['description']) ? $data['description'] : null;
        $this->category = !empty($data['category']) ? $data['category'] : null;
        $this->summary = !empty($data['summary']) ? $data['summary'] : null;
        $this->feedbackurl = !empty($data['feedbackurl']) ? $data['feedbackurl'] : null;
        $this->answer = !empty($data['answer']) ? $data['answer'] : null;
        $this->answerstatus = !empty($data['answerstatus']) ? $data['answerstatus'] : null;
        $this->version = !empty($data['version']) ? $data['version'] : null;
        $this->changelog = !empty($data['changelog']) ? $data['changelog'] : null;
        $this->license = !empty($data['license']) ? $data['license'] : null;
        $this->licensetype = !empty($data['licensetype']) ? $data['licensetype'] : null;
        $this->status = !empty($data['status']) ? $data['status'] : null;
        $this->approved = !empty($data['approved']) ? $data['approved'] : null;
        $this->preview1 = !empty($data['preview1']) ? $data['preview1'] : null;
        $this->preview2 = !empty($data['preview2']) ? $data['preview2'] : null;
        $this->preview3 = !empty($data['preview3']) ? $data['preview3'] : null;
        $this->downloadname1 = !empty($data['downloadname1']) ? $data['downloadname1'] : null;
        $this->downloadtyp1 = !empty($data['downloadtyp1']) ? $data['downloadtyp1'] : null;
        $this->download1 = !empty($data['download1']) ? $data['download1'] : null;
        $this->downloadlink1 = !empty($data['downloadlink1']) ? $data['downloadlink1'] : null;
        $this->downloadfiletype1 = !empty($data['downloadfiletype1']) ? $data['downloadfiletype1'] : null;
        $this->downloadbuy1 = !empty($data['downloadbuy1']) ? $data['downloadbuy1'] : null;
        $this->downloadbuyreason1 = !empty($data['downloadbuyreason1']) ? $data['downloadbuyreason1'] : null;
        $this->downloadbuyprice1 = !empty($data['downloadbuyprice1']) ? $data['downloadbuyprice1'] : null;
        $this->downloadrepository1 = !empty($data['downloadrepository1']) ? $data['downloadrepository1'] : null;
        $this->downloadpackagename1 = !empty($data['downloadpackagename1']) ? $data['downloadpackagename1'] : null;
        $this->downloadgpgfingerprint1 = !empty($data['downloadgpgfingerprint1']) ? $data['downloadgpgfingerprint1'] : null;
        $this->downloadgpgsignature1 = !empty($data['downloadgpgsignature1']) ? $data['downloadgpgsignature1'] : null;
        $this->downloadname2 = !empty($data['downloadname2']) ? $data['downloadname2'] : null;
        $this->downloadversion2 = !empty($data['downloadversion2']) ? $data['downloadversion2'] : null;
        $this->downloadlink2 = !empty($data['downloadlink2']) ? $data['downloadlink2'] : null;
        $this->downloadfiletype2 = !empty($data['downloadfiletype2']) ? $data['downloadfiletype2'] : null;
        $this->downloadbuy2 = !empty($data['downloadbuy2']) ? $data['downloadbuy2'] : null;
        $this->downloadbuyreason2 = !empty($data['downloadbuyreason2']) ? $data['downloadbuyreason2'] : null;
        $this->downloadbuyprice2 = !empty($data['downloadbuyprice2']) ? $data['downloadbuyprice2'] : null;
        $this->downloadrepository2 = !empty($data['downloadrepository2']) ? $data['downloadrepository2'] : null;
        $this->downloadpackagename2 = !empty($data['downloadpackagename2']) ? $data['downloadpackagename2'] : null;
        $this->downloadgpgfingerprint2 = !empty($data['downloadgpgfingerprint2']) ? $data['downloadgpgfingerprint2'] : null;
        $this->downloadgpgsignature2 = !empty($data['downloadgpgsignature2']) ? $data['downloadgpgsignature2'] : null;
        $this->downloadname3 = !empty($data['downloadname3']) ? $data['downloadname3'] : null;
        $this->downloadversion3 = !empty($data['downloadversion3']) ? $data['downloadversion3'] : null;
        $this->downloadlink3 = !empty($data['downloadlink3']) ? $data['downloadlink3'] : null;
        $this->downloadfiletype3 = !empty($data['downloadfiletype3']) ? $data['downloadfiletype3'] : null;
        $this->downloadbuy3 = !empty($data['downloadbuy3']) ? $data['downloadbuy3'] : null;
        $this->downloadbuyreason3 = !empty($data['downloadbuyreason3']) ? $data['downloadbuyreason3'] : null;
        $this->downloadbuyprice3 = !empty($data['downloadbuyprice3']) ? $data['downloadbuyprice3'] : null;
        $this->downloadrepository3 = !empty($data['downloadrepository3']) ? $data['downloadrepository3'] : null;
        $this->downloadpackagename3 = !empty($data['downloadpackagename3']) ? $data['downloadpackagename3'] : null;
        $this->downloadgpgfingerprint3 = !empty($data['downloadgpgfingerprint3']) ? $data['downloadgpgfingerprint3'] : null;
        $this->downloadgpgsignature3 = !empty($data['downloadgpgsignature3']) ? $data['downloadgpgsignature3'] : null;
        $this->downloadname4 = !empty($data['downloadname4']) ? $data['downloadname4'] : null;
        $this->downloadversion4 = !empty($data['downloadversion4']) ? $data['downloadversion4'] : null;
        $this->downloadlink4 = !empty($data['downloadlink4']) ? $data['downloadlink4'] : null;
        $this->downloadfiletype4 = !empty($data['downloadfiletype4']) ? $data['downloadfiletype4'] : null;
        $this->downloadbuy4 = !empty($data['downloadbuy4']) ? $data['downloadbuy4'] : null;
        $this->downloadbuyreason4 = !empty($data['downloadbuyreason4']) ? $data['downloadbuyreason4'] : null;
        $this->downloadbuyprice4 = !empty($data['downloadbuyprice4']) ? $data['downloadbuyprice4'] : null;
        $this->downloadrepository4 = !empty($data['downloadrepository4']) ? $data['downloadrepository4'] : null;
        $this->downloadpackagename4 = !empty($data['downloadpackagename4']) ? $data['downloadpackagename4'] : null;
        $this->downloadgpgfingerprint4 = !empty($data['downloadgpgfingerprint4']) ? $data['downloadgpgfingerprint4'] : null;
        $this->downloadgpgsignature4 = !empty($data['downloadgpgsignature4']) ? $data['downloadgpgsignature4'] : null;
        $this->downloadname5 = !empty($data['downloadname5']) ? $data['downloadname5'] : null;
        $this->downloadversion5 = !empty($data['downloadversion5']) ? $data['downloadversion5'] : null;
        $this->downloadlink5 = !empty($data['downloadlink5']) ? $data['downloadlink5'] : null;
        $this->downloadfiletype5 = !empty($data['downloadfiletype5']) ? $data['downloadfiletype5'] : null;
        $this->downloadbuy5 = !empty($data['downloadbuy5']) ? $data['downloadbuy5'] : null;
        $this->downloadbuyreason5 = !empty($data['downloadbuyreason5']) ? $data['downloadbuyreason5'] : null;
        $this->downloadbuyprice5 = !empty($data['downloadbuyprice5']) ? $data['downloadbuyprice5'] : null;
        $this->downloadrepository5 = !empty($data['downloadrepository5']) ? $data['downloadrepository5'] : null;
        $this->downloadpackagename5 = !empty($data['downloadpackagename5']) ? $data['downloadpackagename5'] : null;
        $this->downloadgpgfingerprint5 = !empty($data['downloadgpgfingerprint5']) ? $data['downloadgpgfingerprint5'] : null;
        $this->downloadgpgsignature5 = !empty($data['downloadgpgsignature5']) ? $data['downloadgpgsignature5'] : null;
        $this->downloadname6 = !empty($data['downloadname6']) ? $data['downloadname6'] : null;
        $this->downloadversion6 = !empty($data['downloadversion6']) ? $data['downloadversion6'] : null;
        $this->downloadlink6 = !empty($data['downloadlink6']) ? $data['downloadlink6'] : null;
        $this->downloadfiletype6 = !empty($data['downloadfiletype6']) ? $data['downloadfiletype6'] : null;
        $this->downloadbuy6 = !empty($data['downloadbuy6']) ? $data['downloadbuy6'] : null;
        $this->downloadbuyreason6 = !empty($data['downloadbuyreason6']) ? $data['downloadbuyreason6'] : null;
        $this->downloadbuyprice6 = !empty($data['downloadbuyprice6']) ? $data['downloadbuyprice6'] : null;
        $this->downloadrepository6 = !empty($data['downloadrepository6']) ? $data['downloadrepository6'] : null;
        $this->downloadpackagename6 = !empty($data['downloadpackagename6']) ? $data['downloadpackagename6'] : null;
        $this->downloadgpgfingerprint6 = !empty($data['downloadgpgfingerprint6']) ? $data['downloadgpgfingerprint6'] : null;
        $this->downloadgpgsignature6 = !empty($data['downloadgpgsignature6']) ? $data['downloadgpgsignature6'] : null;
        $this->downloadlink7 = !empty($data['downloadlink7']) ? $data['downloadlink7'] : null;
        $this->downloadname7 = !empty($data['downloadname7']) ? $data['downloadname7'] : null;
        $this->downloadversion7 = !empty($data['downloadversion7']) ? $data['downloadversion7'] : null;
        $this->downloadfiletype7 = !empty($data['downloadfiletype7']) ? $data['downloadfiletype7'] : null;
        $this->downloadbuy7 = !empty($data['downloadbuy7']) ? $data['downloadbuy7'] : null;
        $this->downloadbuyreason7 = !empty($data['downloadbuyreason7']) ? $data['downloadbuyreason7'] : null;
        $this->downloadbuyprice7 = !empty($data['downloadbuyprice7']) ? $data['downloadbuyprice7'] : null;
        $this->downloadrepository7 = !empty($data['downloadrepository7']) ? $data['downloadrepository7'] : null;
        $this->downloadpackagename7 = !empty($data['downloadpackagename7']) ? $data['downloadpackagename7'] : null;
        $this->downloadgpgfingerprint7 = !empty($data['downloadgpgfingerprint7']) ? $data['downloadgpgfingerprint7'] : null;
        $this->downloadgpgsignature7 = !empty($data['downloadgpgsignature7']) ? $data['downloadgpgsignature7'] : null;
        $this->downloadlink8 = !empty($data['downloadlink8']) ? $data['downloadlink8'] : null;
        $this->downloadname8 = !empty($data['downloadname8']) ? $data['downloadname8'] : null;
        $this->downloadversion8 = !empty($data['downloadversion8']) ? $data['downloadversion8'] : null;
        $this->downloadfiletype8 = !empty($data['downloadfiletype8']) ? $data['downloadfiletype8'] : null;
        $this->downloadbuy8 = !empty($data['downloadbuy8']) ? $data['downloadbuy8'] : null;
        $this->downloadbuyreason8 = !empty($data['downloadbuyreason8']) ? $data['downloadbuyreason8'] : null;
        $this->downloadbuyprice8 = !empty($data['downloadbuyprice8']) ? $data['downloadbuyprice8'] : null;
        $this->downloadrepository8 = !empty($data['downloadrepository8']) ? $data['downloadrepository8'] : null;
        $this->downloadpackagename8 = !empty($data['downloadpackagename8']) ? $data['downloadpackagename8'] : null;
        $this->downloadgpgfingerprint8 = !empty($data['downloadgpgfingerprint8']) ? $data['downloadgpgfingerprint8'] : null;
        $this->downloadgpgsignature8 = !empty($data['downloadgpgsignature8']) ? $data['downloadgpgsignature8'] : null;
        $this->downloadlink9 = !empty($data['downloadlink9']) ? $data['downloadlink9'] : null;
        $this->downloadname9 = !empty($data['downloadname9']) ? $data['downloadname9'] : null;
        $this->downloadversion9 = !empty($data['downloadversion9']) ? $data['downloadversion9'] : null;
        $this->downloadfiletype9 = !empty($data['downloadfiletype9']) ? $data['downloadfiletype9'] : null;
        $this->downloadbuy9 = !empty($data['downloadbuy9']) ? $data['downloadbuy9'] : null;
        $this->downloadbuyreason9 = !empty($data['downloadbuyreason9']) ? $data['downloadbuyreason9'] : null;
        $this->downloadbuyprice9 = !empty($data['downloadbuyprice9']) ? $data['downloadbuyprice9'] : null;
        $this->downloadrepository9 = !empty($data['downloadrepository9']) ? $data['downloadrepository9'] : null;
        $this->downloadpackagename9 = !empty($data['downloadpackagename9']) ? $data['downloadpackagename9'] : null;
        $this->downloadgpgfingerprint9 = !empty($data['downloadgpgfingerprint9']) ? $data['downloadgpgfingerprint9'] : null;
        $this->downloadgpgsignature9 = !empty($data['downloadgpgsignature9']) ? $data['downloadgpgsignature9'] : null;
        $this->downloadlink10 = !empty($data['downloadlink10']) ? $data['downloadlink10'] : null;
        $this->downloadname10 = !empty($data['downloadname10']) ? $data['downloadname10'] : null;
        $this->downloadversion10 = !empty($data['downloadversion10']) ? $data['downloadversion10'] : null;
        $this->downloadfiletype10 = !empty($data['downloadfiletype10']) ? $data['downloadfiletype10'] : null;
        $this->downloadbuy10 = !empty($data['downloadbuy10']) ? $data['downloadbuy10'] : null;
        $this->downloadbuyreason10 = !empty($data['downloadbuyreason10']) ? $data['downloadbuyreason10'] : null;
        $this->downloadbuyprice10 = !empty($data['downloadbuyprice10']) ? $data['downloadbuyprice10'] : null;
        $this->downloadrepository10 = !empty($data['downloadrepository10']) ? $data['downloadrepository10'] : null;
        $this->downloadpackagename10 = !empty($data['downloadpackagename10']) ? $data['downloadpackagename10'] : null;
        $this->downloadgpgfingerprint10 = !empty($data['downloadgpgfingerprint10']) ? $data['downloadgpgfingerprint10'] : null;
        $this->downloadgpgsignature10 = !empty($data['downloadgpgsignature10']) ? $data['downloadgpgsignature10'] : null;
        $this->downloadlink11 = !empty($data['downloadlink11']) ? $data['downloadlink11'] : null;
        $this->downloadname11 = !empty($data['downloadname11']) ? $data['downloadname11'] : null;
        $this->downloadversion11 = !empty($data['downloadversion11']) ? $data['downloadversion11'] : null;
        $this->downloadfiletype11 = !empty($data['downloadfiletype11']) ? $data['downloadfiletype11'] : null;
        $this->downloadbuy11 = !empty($data['downloadbuy11']) ? $data['downloadbuy11'] : null;
        $this->downloadbuyreason11 = !empty($data['downloadbuyreason11']) ? $data['downloadbuyreason11'] : null;
        $this->downloadbuyprice11 = !empty($data['downloadbuyprice11']) ? $data['downloadbuyprice11'] : null;
        $this->downloadrepository11 = !empty($data['downloadrepository11']) ? $data['downloadrepository11'] : null;
        $this->downloadpackagename11 = !empty($data['downloadpackagename11']) ? $data['downloadpackagename11'] : null;
        $this->downloadgpgfingerprint11 = !empty($data['downloadgpgfingerprint11']) ? $data['downloadgpgfingerprint11'] : null;
        $this->downloadgpgsignature12 = !empty($data['downloadgpgsignature12']) ? $data['downloadgpgsignature12'] : null;
        $this->downloadgpgsignature11 = !empty($data['downloadgpgsignature11']) ? $data['downloadgpgsignature11'] : null;
        $this->downloadlink12 = !empty($data['downloadlink12']) ? $data['downloadlink12'] : null;
        $this->downloadname12 = !empty($data['downloadname12']) ? $data['downloadname12'] : null;
        $this->downloadversion12 = !empty($data['downloadversion12']) ? $data['downloadversion12'] : null;
        $this->downloadfiletype12 = !empty($data['downloadfiletype12']) ? $data['downloadfiletype12'] : null;
        $this->downloadbuy12 = !empty($data['downloadbuy12']) ? $data['downloadbuy12'] : null;
        $this->downloadbuyreason12 = !empty($data['downloadbuyreason12']) ? $data['downloadbuyreason12'] : null;
        $this->downloadbuyprice12 = !empty($data['downloadbuyprice12']) ? $data['downloadbuyprice12'] : null;
        $this->downloadrepository12 = !empty($data['downloadrepository12']) ? $data['downloadrepository12'] : null;
        $this->downloadpackagename12 = !empty($data['downloadpackagename12']) ? $data['downloadpackagename12'] : null;
        $this->downloadgpgfingerprint12 = !empty($data['downloadgpgfingerprint12']) ? $data['downloadgpgfingerprint12'] : null;
        $this->osbs_project = !empty($data['osbs_project']) ? $data['osbs_project'] : null;
        $this->osbs_package = !empty($data['osbs_package']) ? $data['osbs_package'] : null;
        $this->scoresum = !empty($data['scoresum']) ? $data['scoresum'] : null;
        $this->scorecount = !empty($data['scorecount']) ? $data['scorecount'] : null;
        $this->homepage1 = !empty($data['homepage1']) ? $data['homepage1'] : null;
        $this->homepagetype1 = !empty($data['homepagetype1']) ? $data['homepagetype1'] : null;
        $this->homepage2 = !empty($data['homepage2']) ? $data['homepage2'] : null;
        $this->homepagetype2 = !empty($data['homepagetype2']) ? $data['homepagetype2'] : null;
        $this->homepage3 = !empty($data['homepage3']) ? $data['homepage3'] : null;
        $this->homepagetype3 = !empty($data['homepagetype3']) ? $data['homepagetype3'] : null;
        $this->homepage4 = !empty($data['homepage4']) ? $data['homepage4'] : null;
        $this->homepagetype4 = !empty($data['homepagetype4']) ? $data['homepagetype4'] : null;
        $this->homepage5 = !empty($data['homepage5']) ? $data['homepage5'] : null;
        $this->homepagetype5 = !empty($data['homepagetype5']) ? $data['homepagetype5'] : null;
        $this->homepage6 = !empty($data['homepage6']) ? $data['homepage6'] : null;
        $this->homepagetype6 = !empty($data['homepagetype6']) ? $data['homepagetype6'] : null;
        $this->homepage7 = !empty($data['homepage7']) ? $data['homepage7'] : null;
        $this->homepagetype7 = !empty($data['homepagetype7']) ? $data['homepagetype7'] : null;
        $this->homepage8 = !empty($data['homepage8']) ? $data['homepage8'] : null;
        $this->homepagetype8 = !empty($data['homepagetype8']) ? $data['homepagetype8'] : null;
        $this->homepage9 = !empty($data['homepage9']) ? $data['homepage9'] : null;
        $this->homepagetype9 = !empty($data['homepagetype9']) ? $data['homepagetype9'] : null;
        $this->homepage10 = !empty($data['homepage10']) ? $data['homepage10'] : null;
        $this->homepagetype10 = !empty($data['homepagetype10']) ? $data['homepagetype10'] : null;
        $this->commentscount = !empty($data['commentscount']) ? $data['commentscount'] : null;
        $this->fancount = !empty($data['fancount']) ? $data['fancount'] : null;
        $this->knowledgebasecount = !empty($data['knowledgebasecount']) ? $data['knowledgebasecount'] : null;
        $this->tags = !empty($data['tags']) ? $data['tags'] : null;
        $this->donation = !empty($data['donation']) ? $data['donation'] : null;
        $this->donationreason = !empty($data['donationreason']) ? $data['donationreason'] : null;
        $this->gnomefiles = !empty($data['gnomefiles']) ? $data['gnomefiles'] : null;
        $this->ean = !empty($data['ean']) ? $data['ean'] : null;
        $this->is_imported = !empty($data['is_imported']) ? $data['is_imported'] : null;
        $this->import_error = !empty($data['import_error']) ? $data['import_error'] : null;
    }

    public function getArrayCopy()
    {
        return [
            'id'                       => $this->id,
            'name'                     => $this->name,
            'user'                     => $this->user,
            'userdb'                   => $this->userdb,
            'created'                  => $this->created,
            'changed'                  => $this->changed,
            'deletedby'                => $this->deletedby,
            'deletedat'                => $this->deletedat,
            'type'                     => $this->type,
            'language'                 => $this->language,
            'depend'                   => $this->depend,
            'depend2'                  => $this->depend2,
            'downloads'                => $this->downloads,
            'scoregood'                => $this->scoregood,
            'scorebad'                 => $this->scorebad,
            'scorelastip1'             => $this->scorelastip1,
            'scorelastip2'             => $this->scorelastip2,
            'scorelastip3'             => $this->scorelastip3,
            'scorelastip4'             => $this->scorelastip4,
            'scorelastip5'             => $this->scorelastip5,
            'scorelastip6'             => $this->scorelastip6,
            'scorelastip7'             => $this->scorelastip7,
            'scorelastip8'             => $this->scorelastip8,
            'scorelastip9'             => $this->scorelastip9,
            'scorelasttime'            => $this->scorelasttime,
            'scorelastagent'           => $this->scorelastagent,
            'scorelastsession1'        => $this->scorelastsession1,
            'scorelastsession2'        => $this->scorelastsession2,
            'scorelastsession3'        => $this->scorelastsession3,
            'description'              => $this->description,
            'category'                 => $this->category,
            'summary'                  => $this->summary,
            'feedbackurl'              => $this->feedbackurl,
            'answer'                   => $this->answer,
            'answerstatus'             => $this->answerstatus,
            'version'                  => $this->version,
            'changelog'                => $this->changelog,
            'license'                  => $this->license,
            'licensetype'              => $this->licensetype,
            'status'                   => $this->status,
            'approved'                 => $this->approved,
            'preview1'                 => $this->preview1,
            'preview2'                 => $this->preview2,
            'preview3'                 => $this->preview3,
            'downloadname1'            => $this->downloadname1,
            'downloadtyp1'             => $this->downloadtyp1,
            'download1'                => $this->download1,
            'downloadlink1'            => $this->downloadlink1,
            'downloadfiletype1'        => $this->downloadfiletype1,
            'downloadbuy1'             => $this->downloadbuy1,
            'downloadbuyreason1'       => $this->downloadbuyreason1,
            'downloadbuyprice1'        => $this->downloadbuyprice1,
            'downloadrepository1'      => $this->downloadrepository1,
            'downloadpackagename1'     => $this->downloadpackagename1,
            'downloadgpgfingerprint1'  => $this->downloadgpgfingerprint1,
            'downloadgpgsignature1'    => $this->downloadgpgsignature1,
            'downloadname2'            => $this->downloadname2,
            'downloadversion2'         => $this->downloadversion2,
            'downloadlink2'            => $this->downloadlink2,
            'downloadfiletype2'        => $this->downloadfiletype2,
            'downloadbuy2'             => $this->downloadbuy2,
            'downloadbuyreason2'       => $this->downloadbuyreason2,
            'downloadbuyprice2'        => $this->downloadbuyprice2,
            'downloadrepository2'      => $this->downloadrepository2,
            'downloadpackagename2'     => $this->downloadpackagename2,
            'downloadgpgfingerprint2'  => $this->downloadgpgfingerprint2,
            'downloadgpgsignature2'    => $this->downloadgpgsignature2,
            'downloadname3'            => $this->downloadname3,
            'downloadversion3'         => $this->downloadversion3,
            'downloadlink3'            => $this->downloadlink3,
            'downloadfiletype3'        => $this->downloadfiletype3,
            'downloadbuy3'             => $this->downloadbuy3,
            'downloadbuyreason3'       => $this->downloadbuyreason3,
            'downloadbuyprice3'        => $this->downloadbuyprice3,
            'downloadrepository3'      => $this->downloadrepository3,
            'downloadpackagename3'     => $this->downloadpackagename3,
            'downloadgpgfingerprint3'  => $this->downloadgpgfingerprint3,
            'downloadgpgsignature3'    => $this->downloadgpgsignature3,
            'downloadname4'            => $this->downloadname4,
            'downloadversion4'         => $this->downloadversion4,
            'downloadlink4'            => $this->downloadlink4,
            'downloadfiletype4'        => $this->downloadfiletype4,
            'downloadbuy4'             => $this->downloadbuy4,
            'downloadbuyreason4'       => $this->downloadbuyreason4,
            'downloadbuyprice4'        => $this->downloadbuyprice4,
            'downloadrepository4'      => $this->downloadrepository4,
            'downloadpackagename4'     => $this->downloadpackagename4,
            'downloadgpgfingerprint4'  => $this->downloadgpgfingerprint4,
            'downloadgpgsignature4'    => $this->downloadgpgsignature4,
            'downloadname5'            => $this->downloadname5,
            'downloadversion5'         => $this->downloadversion5,
            'downloadlink5'            => $this->downloadlink5,
            'downloadfiletype5'        => $this->downloadfiletype5,
            'downloadbuy5'             => $this->downloadbuy5,
            'downloadbuyreason5'       => $this->downloadbuyreason5,
            'downloadbuyprice5'        => $this->downloadbuyprice5,
            'downloadrepository5'      => $this->downloadrepository5,
            'downloadpackagename5'     => $this->downloadpackagename5,
            'downloadgpgfingerprint5'  => $this->downloadgpgfingerprint5,
            'downloadgpgsignature5'    => $this->downloadgpgsignature5,
            'downloadname6'            => $this->downloadname6,
            'downloadversion6'         => $this->downloadversion6,
            'downloadlink6'            => $this->downloadlink6,
            'downloadfiletype6'        => $this->downloadfiletype6,
            'downloadbuy6'             => $this->downloadbuy6,
            'downloadbuyreason6'       => $this->downloadbuyreason6,
            'downloadbuyprice6'        => $this->downloadbuyprice6,
            'downloadrepository6'      => $this->downloadrepository6,
            'downloadpackagename6'     => $this->downloadpackagename6,
            'downloadgpgfingerprint6'  => $this->downloadgpgfingerprint6,
            'downloadgpgsignature6'    => $this->downloadgpgsignature6,
            'downloadlink7'            => $this->downloadlink7,
            'downloadname7'            => $this->downloadname7,
            'downloadversion7'         => $this->downloadversion7,
            'downloadfiletype7'        => $this->downloadfiletype7,
            'downloadbuy7'             => $this->downloadbuy7,
            'downloadbuyreason7'       => $this->downloadbuyreason7,
            'downloadbuyprice7'        => $this->downloadbuyprice7,
            'downloadrepository7'      => $this->downloadrepository7,
            'downloadpackagename7'     => $this->downloadpackagename7,
            'downloadgpgfingerprint7'  => $this->downloadgpgfingerprint7,
            'downloadgpgsignature7'    => $this->downloadgpgsignature7,
            'downloadlink8'            => $this->downloadlink8,
            'downloadname8'            => $this->downloadname8,
            'downloadversion8'         => $this->downloadversion8,
            'downloadfiletype8'        => $this->downloadfiletype8,
            'downloadbuy8'             => $this->downloadbuy8,
            'downloadbuyreason8'       => $this->downloadbuyreason8,
            'downloadbuyprice8'        => $this->downloadbuyprice8,
            'downloadrepository8'      => $this->downloadrepository8,
            'downloadpackagename8'     => $this->downloadpackagename8,
            'downloadgpgfingerprint8'  => $this->downloadgpgfingerprint8,
            'downloadgpgsignature8'    => $this->downloadgpgsignature8,
            'downloadlink9'            => $this->downloadlink9,
            'downloadname9'            => $this->downloadname9,
            'downloadversion9'         => $this->downloadversion9,
            'downloadfiletype9'        => $this->downloadfiletype9,
            'downloadbuy9'             => $this->downloadbuy9,
            'downloadbuyreason9'       => $this->downloadbuyreason9,
            'downloadbuyprice9'        => $this->downloadbuyprice9,
            'downloadrepository9'      => $this->downloadrepository9,
            'downloadpackagename9'     => $this->downloadpackagename9,
            'downloadgpgfingerprint9'  => $this->downloadgpgfingerprint9,
            'downloadgpgsignature9'    => $this->downloadgpgsignature9,
            'downloadlink10'           => $this->downloadlink10,
            'downloadname10'           => $this->downloadname10,
            'downloadversion10'        => $this->downloadversion10,
            'downloadfiletype10'       => $this->downloadfiletype10,
            'downloadbuy10'            => $this->downloadbuy10,
            'downloadbuyreason10'      => $this->downloadbuyreason10,
            'downloadbuyprice10'       => $this->downloadbuyprice10,
            'downloadrepository10'     => $this->downloadrepository10,
            'downloadpackagename10'    => $this->downloadpackagename10,
            'downloadgpgfingerprint10' => $this->downloadgpgfingerprint10,
            'downloadgpgsignature10'   => $this->downloadgpgsignature10,
            'downloadlink11'           => $this->downloadlink11,
            'downloadname11'           => $this->downloadname11,
            'downloadversion11'        => $this->downloadversion11,
            'downloadfiletype11'       => $this->downloadfiletype11,
            'downloadbuy11'            => $this->downloadbuy11,
            'downloadbuyreason11'      => $this->downloadbuyreason11,
            'downloadbuyprice11'       => $this->downloadbuyprice11,
            'downloadrepository11'     => $this->downloadrepository11,
            'downloadpackagename11'    => $this->downloadpackagename11,
            'downloadgpgfingerprint11' => $this->downloadgpgfingerprint11,
            'downloadgpgsignature12'   => $this->downloadgpgsignature12,
            'downloadgpgsignature11'   => $this->downloadgpgsignature11,
            'downloadlink12'           => $this->downloadlink12,
            'downloadname12'           => $this->downloadname12,
            'downloadversion12'        => $this->downloadversion12,
            'downloadfiletype12'       => $this->downloadfiletype12,
            'downloadbuy12'            => $this->downloadbuy12,
            'downloadbuyreason12'      => $this->downloadbuyreason12,
            'downloadbuyprice12'       => $this->downloadbuyprice12,
            'downloadrepository12'     => $this->downloadrepository12,
            'downloadpackagename12'    => $this->downloadpackagename12,
            'downloadgpgfingerprint12' => $this->downloadgpgfingerprint12,
            'osbs_project'             => $this->osbs_project,
            'osbs_package'             => $this->osbs_package,
            'scoresum'                 => $this->scoresum,
            'scorecount'               => $this->scorecount,
            'homepage1'                => $this->homepage1,
            'homepagetype1'            => $this->homepagetype1,
            'homepage2'                => $this->homepage2,
            'homepagetype2'            => $this->homepagetype2,
            'homepage3'                => $this->homepage3,
            'homepagetype3'            => $this->homepagetype3,
            'homepage4'                => $this->homepage4,
            'homepagetype4'            => $this->homepagetype4,
            'homepage5'                => $this->homepage5,
            'homepagetype5'            => $this->homepagetype5,
            'homepage6'                => $this->homepage6,
            'homepagetype6'            => $this->homepagetype6,
            'homepage7'                => $this->homepage7,
            'homepagetype7'            => $this->homepagetype7,
            'homepage8'                => $this->homepage8,
            'homepagetype8'            => $this->homepagetype8,
            'homepage9'                => $this->homepage9,
            'homepagetype9'            => $this->homepagetype9,
            'homepage10'               => $this->homepage10,
            'homepagetype10'           => $this->homepagetype10,
            'commentscount'            => $this->commentscount,
            'fancount'                 => $this->fancount,
            'knowledgebasecount'       => $this->knowledgebasecount,
            'tags'                     => $this->tags,
            'donation'                 => $this->donation,
            'donationreason'           => $this->donationreason,
            'gnomefiles'               => $this->gnomefiles,
            'ean'                      => $this->ean,
            'is_imported'              => $this->is_imported,
            'import_error'             => $this->import_error,
        ];
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new DomainException(
            sprintf(
                '%s does not allow injection of an alternate input filter', __CLASS__
            )
        );
    }

    public function getInputFilter()
    {
        if ($this->inputFilter) {
            return $this->inputFilter;
        }

        $inputFilter = new InputFilter();
        //-----------------> hier come inputfiler
        $this->inputFilter = $inputFilter;

        return $this->inputFilter;
    }
}
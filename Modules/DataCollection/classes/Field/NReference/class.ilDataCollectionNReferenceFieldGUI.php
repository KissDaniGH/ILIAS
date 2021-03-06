<?php

/**
 * Class ilDataCollectionNReferenceFieldGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDataCollectionNReferenceFieldGUI {

	/**
	 * @var ilDataCollectionNReferenceField
	 */
	protected $field;


	/**
	 * @param ilDataCollectionNReferenceField $field
	 */
	public function __construct(ilDataCollectionNReferenceField $field) {
		$this->field = $field;
	}


	/**
	 * @param ilDataCollectionNReferenceField $field
	 * @param null                            $options
	 *
	 * @return string
	 */
	public function getSingleHTML($options = NULL) {
		$values = $this->field->getValue();

		if (! $values || ! count($values)) {
			return "";
		}

		$tpl = $this->buildTemplate($this->field, $values, $options);

		return $tpl->get();
	}


	/**
	 * @param $record_field
	 * @param $values
	 * @param $options
	 *
	 * @return ilTemplate
	 */
	protected function buildTemplate(ilDataCollectionNReferenceField $record_field, $values, $options) {
		$tpl = new ilTemplate("tpl.reference_list.html", true, true, "Modules/DataCollection");
		$tpl->setCurrentBlock("reference_list");
		foreach ($values as $value) {
			$ref_record = ilDataCollectionCache::getRecordCache($value);
			if (! $ref_record->getTableId() || ! $record_field->getField() || ! $record_field->getField()->getTableId()) {
				//the referenced record_field does not seem to exist.
				$record_field->setValue(0);
				$record_field->doUpdate();
			} else {
				$tpl->setCurrentBlock("reference");
				if (! $options) {
					$tpl->setVariable("CONTENT", $ref_record->getRecordFieldHTML($record_field->getField()->getFieldRef()));
				} else {
					$tpl->setVariable("CONTENT", $record_field->getLinkHTML($options['link']['name'], $value));
				}
				$tpl->parseCurrentBlock();
			}
		}
		$tpl->parseCurrentBlock();

		return $tpl;
	}


	/**
	 * @return array|mixed|string
	 */
	public function getHTML() {
		$values = $this->field->getValue();
		$record_field = $this->field;

		if (! $values OR ! count($values)) {
			return "";
		}

		$html = "";
		$cut = false;
		$tpl = new ilTemplate("tpl.reference_hover.html", true, true, "Modules/DataCollection");
		$tpl->setCurrentBlock("reference_list");
		foreach ($values as $value) {
			$ref_record = ilDataCollectionCache::getRecordCache($value);
			if (! $ref_record->getTableId() OR ! $record_field->getField() OR ! $record_field->getField()->getTableId()) {
				//the referenced record_field does not seem to exist.
				$record_field->setValue(NULL);
				$record_field->doUpdate();
			} else {
				if ((strlen($html) < $record_field->getMaxReferenceLength())) {
					$html .= $ref_record->getRecordFieldHTML($record_field->getField()->getFieldRef()) . ", ";
				} else {
					$cut = true;
				}
				$tpl->setCurrentBlock("reference");
				$tpl->setVariable("CONTENT", $ref_record->getRecordFieldHTML($record_field->getField()->getFieldRef()));
				$tpl->parseCurrentBlock();
			}
		}
		$html = substr($html, 0, - 2);
		if ($cut) {
			$html .= "...";
		}
		$tpl->setVariable("RECORD_ID", $record_field->getRecord()->getId());
		$tpl->setVariable("ALL", $html);
		$tpl->parseCurrentBlock();

		return $tpl->get();
	}
}

?>
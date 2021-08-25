<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%individual_assessment_detail}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%items}}`
 * - `{{%marker_student_info}}`
 */
class m210802_171547_create_individual_assessment_detail_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%individual_assessment_detail}}', [
            'id' => $this->primaryKey(),
            'item_id' => $this->integer(11)->notNull(),
            'mark' => $this->integer(3)->notNull(),
            'comment' => 'LONGTEXT',
            'marker_student_info_id' => $this->integer(11),
        ]);

        // creates index for column `item_id`
        $this->createIndex(
            '{{%idx-individual_assessment_detail-item_id}}',
            '{{%individual_assessment_detail}}',
            'item_id'
        );

        // add foreign key for table `{{%items}}`
        $this->addForeignKey(
            '{{%fk-individual_assessment_detail-item_id}}',
            '{{%individual_assessment_detail}}',
            'item_id',
            '{{%items}}',
            'id',
            'CASCADE'
        );

        // creates index for column `marker_student_info_id`
        $this->createIndex(
            '{{%idx-individual_assessment_detail-marker_student_info_id}}',
            '{{%individual_assessment_detail}}',
            'marker_student_info_id'
        );

        // add foreign key for table `{{%marker_student_info}}`
        $this->addForeignKey(
            '{{%fk-individual_assessment_detail-marker_student_info_id}}',
            '{{%individual_assessment_detail}}',
            'marker_student_info_id',
            '{{%marker_student_info}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%items}}`
        $this->dropForeignKey(
            '{{%fk-individual_assessment_detail-item_id}}',
            '{{%individual_assessment_detail}}'
        );

        // drops index for column `item_id`
        $this->dropIndex(
            '{{%idx-individual_assessment_detail-item_id}}',
            '{{%individual_assessment_detail}}'
        );

        // drops foreign key for table `{{%marker_student_info}}`
        $this->dropForeignKey(
            '{{%fk-individual_assessment_detail-marker_student_info_id}}',
            '{{%individual_assessment_detail}}'
        );

        // drops index for column `marker_student_info_id`
        $this->dropIndex(
            '{{%idx-individual_assessment_detail-marker_student_info_id}}',
            '{{%individual_assessment_detail}}'
        );

        $this->dropTable('{{%individual_assessment_detail}}');
    }
}

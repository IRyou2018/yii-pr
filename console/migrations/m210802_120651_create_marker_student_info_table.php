<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%marker_student_info}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%individual_assessment}}`
 */
class m210802_120651_create_marker_student_info_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%marker_student_info}}', [
            'id' => $this->primaryKey(),
            'marker_student_id' => $this->integer(11)->notNull(),
            'individual_assessment_id' => $this->integer(11)->notNull(),
            'completed' => $this->boolean(),
        ]);

        // creates index for column `marker_student_id`
        $this->createIndex(
            '{{%idx-marker_student_info-marker_student_id}}',
            '{{%marker_student_info}}',
            'marker_student_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-marker_student_info-marker_student_id}}',
            '{{%marker_student_info}}',
            'marker_student_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `individual_assessment_id`
        $this->createIndex(
            '{{%idx-marker_student_info-individual_assessment_id}}',
            '{{%marker_student_info}}',
            'individual_assessment_id'
        );

        // add foreign key for table `{{%individual_assessment}}`
        $this->addForeignKey(
            '{{%fk-marker_student_info-individual_assessment_id}}',
            '{{%marker_student_info}}',
            'individual_assessment_id',
            '{{%individual_assessment}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-marker_student_info-marker_student_id}}',
            '{{%marker_student_info}}'
        );

        // drops index for column `marker_student_id`
        $this->dropIndex(
            '{{%idx-marker_student_info-marker_student_id}}',
            '{{%marker_student_info}}'
        );

        // drops foreign key for table `{{%individual_assessment}}`
        $this->dropForeignKey(
            '{{%fk-marker_student_info-individual_assessment_id}}',
            '{{%marker_student_info}}'
        );

        // drops index for column `individual_assessment_id`
        $this->dropIndex(
            '{{%idx-marker_student_info-individual_assessment_id}}',
            '{{%marker_student_info}}'
        );

        $this->dropTable('{{%marker_student_info}}');
    }
}

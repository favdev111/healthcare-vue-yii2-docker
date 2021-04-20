<?php

namespace api2\helpers;

use yii\helpers\ArrayHelper;

final class DoctorType
{
    //doctors
    public const ALLERGIST_IMMUNOLOGIST = 1;
    public const ANAESTHESIOLOGIST = 2;
    public const ANDROLOGIST = 3;
    public const CARDIOLOGIST = 4;
    public const CARDIAC_ELECTROPHYSIOLOGIST = 5;
    public const CRITICAL_CARE_MEDICINE_SPECIALISTS = 6;
    public const DERMATOLOGIST = 7;
    public const EMERGENCY_ROOM_ER_DOCTORS = 8;
    public const ENDOCRINOLOGIST = 9;
    public const EPIDEMIOLOGIST = 10;
    public const FAMILY_PHYSICIAN = 11;
    public const GASTROENTEROLOGIST = 12;
    public const GENERAL_SURGEONS = 13;
    public const GERIATRICIAN = 14;
    public const HOSPICE_AND_PALLIATIVE_SPECIALISTS = 15;
    public const HYPERBARIC_PHYSICIAN = 16;
    public const HEMATOLOGIST = 17;
    public const HEPATOLOGIST = 18;
    public const IMMUNOLOGIST = 19;
    public const INFECTIOUS_DISEASE_SPECIALIST = 20;
    public const INTENSIVIST = 21;
    public const INTERNAL_MEDICINE_SPECIALIST = 22;
    public const MAXILLOFACIAL_SURGEON_ORAL_SURGEON = 23;
    public const MEDICAL_EXAMINER = 24;
    public const MEDICAL_GENETICIST = 25;
    public const NEONATOLOGIST = 26;
    public const NEPHROLOGIST = 27;
    public const NEUROLOGIST = 28;
    public const NEUROSURGEON = 29;
    public const NUCLEAR_MEDICINE_SPECIALIST = 30;
    public const OBSTETRICIAN_GYNECOLOGIST_OB_GYN = 31;
    public const OCCUPATIONAL_MEDICINE_SPECIALIST = 32;
    public const ONCOLOGIST = 34;
    public const OPHTHALMOLOGIST = 35;
    public const ORTHOPEDIC_SURGEON_ORTHOPEDIST = 36;
    public const OSTEOPATHS = 37;
    public const OTOLARYNGOLOGIST_ALSO_ENT_SPECIALIST = 38;
    public const PARASITOLOGIST = 39;
    public const PATHOLOGIST = 40;
    public const PERINATOLOGIST = 41;
    public const PERIODONTIST = 42;
    public const PEDIATRICIAN = 43;
    public const PHYSIATRIST = 44;
    public const PLASTIC_SURGEON = 45;
    public const PREVENTIVE_MEDICINE_SPECIALISTS = 46;
    public const PSYCHIATRIST = 47;
    public const PULMONOLOGIST = 48;
    public const RADIOLOGIST = 49;
    public const RHEUMATOLOGIST = 50;
    public const SLEEP_SPECIALIST = 51;
    public const SPINAL_CORD_INJURY_SPECIALIST = 52;
    public const SPORTS_MEDICINE_SPECIALIST = 53;
    public const SURGEON = 54;
    public const THORACIC_SURGEON = 55;
    public const UROLOGIST = 56;
    public const VASCULAR_SURGEON = 57;
    public const VETERINARIAN = 58;

    //Nurse types
    public const ACNP = 59;
    public const AG_ACNP = 60;
    public const AG_PCN = 61;
    public const CARDIAC_NURSE_P = 62;
    public const FAMILY_NURSE_P = 63;
    public const GERONTOLOGICAL_NURSE_P = 64;
    public const NNP = 65;
    public const ONCOLOGY_NURSE_P = 66;
    public const ORTHOPEDIC_NURSE_P = 67;
    public const PNP_AC = 68;
    public const PEDIATRIC_NURSE_P = 69;
    public const PMHNP = 70;
    public const WHNP = 71;

    public const DOCTORS_SPECIALIZATION_LABELS = [
        self::ALLERGIST_IMMUNOLOGIST => 'Allergist/Immunologist',
        self::ANAESTHESIOLOGIST => 'Anaesthesiologist',
        self::ANDROLOGIST => 'Andrologist',
        self::CARDIOLOGIST => 'Cardiologist',
        self::CARDIAC_ELECTROPHYSIOLOGIST => 'Cardiac Electrophysiologist',
        self::CRITICAL_CARE_MEDICINE_SPECIALISTS => 'Critical Care Medicine Specialists',
        self::DERMATOLOGIST => 'Dermatologist',
        self::EMERGENCY_ROOM_ER_DOCTORS => 'Emergency Room (ER) Doctors',
        self::ENDOCRINOLOGIST => 'Endocrinologist',
        self::EPIDEMIOLOGIST => 'Epidemiologist',
        self::FAMILY_PHYSICIAN => 'Family Physician',
        self::GASTROENTEROLOGIST => 'Gastroenterologist',
        self::GENERAL_SURGEONS => 'General Surgeons',
        self::GERIATRICIAN => 'Geriatrician',
        self::HOSPICE_AND_PALLIATIVE_SPECIALISTS => 'Hospice and Palliative Specialists',
        self::HYPERBARIC_PHYSICIAN => 'Hyperbaric Physician',
        self::HEMATOLOGIST => 'Hematologist',
        self::HEPATOLOGIST => 'Hepatologist',
        self::IMMUNOLOGIST => 'Immunologist',
        self::INFECTIOUS_DISEASE_SPECIALIST => 'Infectious Disease Specialist',
        self::INTENSIVIST => 'Intensivist',
        self::INTERNAL_MEDICINE_SPECIALIST => 'Internal Medicine Specialist',
        self::MAXILLOFACIAL_SURGEON_ORAL_SURGEON => 'Maxillofacial Surgeon / Oral Surgeon',
        self::MEDICAL_EXAMINER => 'Medical Examiner',
        self::MEDICAL_GENETICIST => 'Medical Geneticist',
        self::NEONATOLOGIST => 'Neonatologist',
        self::NEPHROLOGIST => 'Nephrologist',
        self::NEUROLOGIST => 'Neurologist',
        self::NEUROSURGEON => 'Neurosurgeon',
        self::NUCLEAR_MEDICINE_SPECIALIST => 'Nuclear Medicine Specialist',
        self::OBSTETRICIAN_GYNECOLOGIST_OB_GYN => 'Obstetrician/Gynecologist (OB/GYN)',
        self::OCCUPATIONAL_MEDICINE_SPECIALIST => 'Occupational Medicine Specialist',
        self::ONCOLOGIST => 'Oncologist',
        self::OPHTHALMOLOGIST => 'Ophthalmologist',
        self::ORTHOPEDIC_SURGEON_ORTHOPEDIST => 'Orthopedic Surgeon / Orthopedist',
        self::OSTEOPATHS => 'Osteopaths',
        self::OTOLARYNGOLOGIST_ALSO_ENT_SPECIALIST => 'Otolaryngologist (also ENT Specialist)',
        self::PARASITOLOGIST => 'Parasitologist',
        self::PATHOLOGIST => 'Pathologist',
        self::PERINATOLOGIST => 'Perinatologist',
        self::PERIODONTIST => 'Periodontist',
        self::PEDIATRICIAN => 'Pediatrician',
        self::PHYSIATRIST => 'Physiatrist',
        self::PLASTIC_SURGEON => 'Plastic Surgeon',
        self::PREVENTIVE_MEDICINE_SPECIALISTS => 'Preventive Medicine Specialists',
        self::PSYCHIATRIST => 'Psychiatrist',
        self::PULMONOLOGIST => 'Pulmonologist',
        self::RADIOLOGIST => 'Radiologist',
        self::RHEUMATOLOGIST => 'Rheumatologist',
        self::SLEEP_SPECIALIST => 'Sleep Specialist',
        self::SPINAL_CORD_INJURY_SPECIALIST => 'Spinal Cord Injury Specialist',
        self::SPORTS_MEDICINE_SPECIALIST => 'Sports Medicine Specialist',
        self::SURGEON => 'Surgeon',
        self::THORACIC_SURGEON => 'Thoracic Surgeon',
        self::UROLOGIST => 'Urologist',
        self::VASCULAR_SURGEON => 'Vascular Surgeon',
        self::VETERINARIAN => 'Veterinarian',
    ];

    public const NURSE_SPECIALIZATION_LABELS = [
        //nurse types
        self::ACNP => "Acute Care Nurse Practitioner (ACNP)",
        self::AG_ACNP => "Adult Gerontology Acute Care Nurse Practitioner (AG-ACNP)",
        self::AG_PCN => "Adult Gerontology Primary Care Nurse Practitioner (AG-PCNP)",
        self::CARDIAC_NURSE_P => "Cardiac Nurse Practitioner",
        self::FAMILY_NURSE_P => "Family Nurse Practitioner",
        self::GERONTOLOGICAL_NURSE_P => "Gerontological Nurse Practitioner",
        self::NNP => "Neonatal Nurse Practitioner (NNP)",
        self::ONCOLOGY_NURSE_P => "Oncology Nurse Practitioner",
        self::ORTHOPEDIC_NURSE_P => "Orthopedic Nurse Practitioner",
        self::PNP_AC => "Pediatric Acute Care Nurse Practitioner (PNP-AC)",
        self::PEDIATRIC_NURSE_P => "Pediatric Nurse Practitioner",
        self::PMHNP => "Psychiatric and Mental Health Nurse Practitioner (PMHNP)",
        self::WHNP => "Women’s Health Nurse Practitioner (WHNP)",
    ];

    public static function getDoctorTypes(): array
    {
        return array_keys(self::DOCTORS_SPECIALIZATION_LABELS);
    }

    public static function getNurseTypes(): array
    {
        return array_keys(self::NURSE_SPECIALIZATION_LABELS);
    }

    public static function getAllLabels(): array
    {
        return array_merge(self::DOCTORS_SPECIALIZATION_LABELS, self::NURSE_SPECIALIZATION_LABELS);
    }

    /**
     * @param $id
     * @return string|null
     * @throws \Exception
     */
    public static function getDoctorType($id): ?string
    {
        return ArrayHelper::getValue(self::DOCTORS_SPECIALIZATION_LABELS, $id);
    }
}

export interface JobAvailable {
  job_id: number | string;
  job_name: string;
  job_description?: string;
  job_min_students?: number;
  job_max_students?: number;
  job_duration?: number;
  job_unit_name: string;
}

export interface JobDone {
  job_name: string;
  registration_id: number | string;
  job_unit_id: number | string;
  job_unit_name: string;
  job_description?: string;
  job_is_complete?: boolean | string;
  start_date?: string;
  end_date?: string;
  group_name?: string;
  lead_email?: string;
  click_date?: string;
  job_id?: number | string;
  group_id?: number | string;
}

export interface JobInProgress {
  job_id: number | string;
  job_name: string;
  start_date: string;
  end_date: string;
  registration_id: number | string;
  group_id?: number | string;
  job_is_done: boolean;
  job_link_subject?: string;
  job_unit_name?: string;
  job_unit_id?: number | string;
  job_description?: string;
  job_duration?: number;
}

export interface JobUnit {
  unit_id: number | string;
  unit_name: string;
  promotion_id: number | string;
}

export interface JobSkills {
  skill_id: number | string;
  skill_name: string;
  needed: boolean;
  earned: boolean;
}

export interface JobGroupMembers {
  group_id: number | string;
  member: {
    email: string;
    is_lead: boolean;
    registration_id: number | string;
  }[];
}

export interface JobGroups {
  group_id: number | string;
  group_name: string;
  lead_firstname: string;
  lead_lastname: string;
  lead_email?: string;
}

export interface JobReview {
  job_id: number | string;
  job_name: string;
  job_unit_name: string;
  job_unit_id: number | string;
  job_description?: string;
  job_duration?: number;
  group_id: number | string;
  group_name: string;
  lead_email: string;
  lead_firstname: string;
  lead_lastname: string;
  registration_id: number | string;
  job_is_complete: boolean;
  job_is_done: boolean;
  start_date: string;
  end_date: string;
  click_date: string;
  correction_date?: string;
  corrector: string;
  comment: string;
  skill: {
    skill_id: number | string;
    skill_name: string;
    job_skill_needed: boolean;
    job_skill_earned: boolean;
    skill_status: string;
  }[];
}
export interface JobPromotion {
  promotion_id: number | string;
  promotion_name: string;
}
export interface AvailableModalProps {
  visible: boolean;
  job: JobAvailable | null;
  onClose: () => void;
}
export interface ProgressModalProps {
  visible: boolean;
  job: JobInProgress | null;
  onClose: () => void;
  onReport: () => void;
}
export interface GroupManagementModalProps {
  visible: boolean;
  jobId: number | string | null;
  onClose: () => void;
}

export interface ReviewModalProps {
  visible: boolean;
  groupId: number | string | null;
  onClose: () => void;
}

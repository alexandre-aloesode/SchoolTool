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
}

export interface JobInProgress {
  job_id: number | string;
  job_name: string;
  start_date: string;
  end_date: string;
  registration_id: number | string;
  group_id: number | string;
  job_is_done: boolean;
}

export interface JobUnit {
  unit_id: number | string;
  unit_name: string;
  promotion_id: number | string;
}

export interface JobPromotion {
  promotion_id: number | string;
  promotion_name: string;
}

export interface AbsenceForm {
  start_date: string;
  end_date: string;
  duration: number;
  reason: string;
  image: string | null;
  imageName: string;
}

export interface UploadedAbsence {
  absence_start_date: string;
  absence_end_date: string;
  absence_duration: number;
  absence_status: number;
  absence_comment: string | null;
}

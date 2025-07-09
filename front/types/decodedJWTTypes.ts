export interface DecodedJWT {
  user_id: string;
  user_email: string;
  role: string;
  scope: string[];
}

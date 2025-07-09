export type GoogleCalendarEvent = {
  id: string;
  summary: string;
  start: {
    dateTime?: string;
    date?: string;
  };
  end?: {
    dateTime?: string;
    date?: string;
  };
  description?: string;
  location?: string;
  htmlLink?: string;
  attendees?: Array<{
    email: string;
    responseStatus: string;
  }>;
};

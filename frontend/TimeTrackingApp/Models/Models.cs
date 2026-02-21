namespace TimeTrackingApp.Models;

public class UserInfo
{
    public int Id { get; set; }
    public string Username { get; set; } = "";
    public string DisplayName { get; set; } = "";
}

public class LoginRequest
{
    public string Username { get; set; } = "";
    public string Password { get; set; } = "";
}

public class LoginResponse
{
    public string Token { get; set; } = "";
    public UserInfo User { get; set; } = new();
}

public class Workstation
{
    public int Id { get; set; }
    public string Code { get; set; } = "";
    public string Label { get; set; } = "";
}

public class TimeEntry
{
    public int Id { get; set; }
    public string EntryDate { get; set; } = "";
    public string Period { get; set; } = "";
    public int Slot { get; set; }
    public int WorkstationId { get; set; }
    public string WorkstationCode { get; set; } = "";
    public string WorkstationLabel { get; set; } = "";
}

public class TimeEntryRequest
{
    public string Date { get; set; } = "";
    public string Period { get; set; } = "";
    public int Slot { get; set; }
    public int WorkstationId { get; set; }
}

public class SaveEntriesRequest
{
    public List<TimeEntryRequest> Entries { get; set; } = new();
}

public class StatsResponse
{
    public string From { get; set; } = "";
    public string To { get; set; } = "";
    public decimal TotalHours { get; set; }
    public List<WorkstationStat> ByWorkstation { get; set; } = new();
    public List<DayStat> ByDay { get; set; } = new();
}

public class WorkstationStat
{
    public string Code { get; set; } = "";
    public string Label { get; set; } = "";
    public decimal TotalHours { get; set; }
}

public class DayStat
{
    public string EntryDate { get; set; } = "";
    public decimal TotalHours { get; set; }
}

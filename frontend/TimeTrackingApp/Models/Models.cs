using System.Text.Json.Serialization;

namespace TimeTrackingApp.Models;

public class UserInfo
{
    [JsonPropertyName("id")]
    public int Id { get; set; }

    [JsonPropertyName("username")]
    public string Username { get; set; } = "";

    [JsonPropertyName("displayName")]
    public string DisplayName { get; set; } = "";

    [JsonPropertyName("isAdmin")]
    public bool IsAdmin { get; set; }
}

public class UserListItem
{
    [JsonPropertyName("id")]
    public int Id { get; set; }

    [JsonPropertyName("username")]
    public string Username { get; set; } = "";

    [JsonPropertyName("display_name")]
    public string DisplayName { get; set; } = "";

    [JsonPropertyName("is_admin")]
    public bool IsAdmin { get; set; }

    [JsonPropertyName("is_active")]
    public bool IsActive { get; set; }

    [JsonPropertyName("created_at")]
    public string CreatedAt { get; set; } = "";
}

public class CreateUserRequest
{
    [JsonPropertyName("username")]
    public string Username { get; set; } = "";

    [JsonPropertyName("password")]
    public string Password { get; set; } = "";

    [JsonPropertyName("display_name")]
    public string DisplayName { get; set; } = "";

    [JsonPropertyName("is_admin")]
    public bool IsAdmin { get; set; }
}

public class UpdateUserRequest
{
    [JsonPropertyName("id")]
    public int Id { get; set; }

    [JsonPropertyName("username")]
    public string? Username { get; set; }

    [JsonPropertyName("display_name")]
    public string? DisplayName { get; set; }

    [JsonPropertyName("password")]
    public string? Password { get; set; }

    [JsonPropertyName("is_admin")]
    public bool? IsAdmin { get; set; }

    [JsonPropertyName("is_active")]
    public bool? IsActive { get; set; }
}

public class LoginRequest
{
    [JsonPropertyName("username")]
    public string Username { get; set; } = "";

    [JsonPropertyName("password")]
    public string Password { get; set; } = "";
}

public class LoginResponse
{
    [JsonPropertyName("token")]
    public string Token { get; set; } = "";

    [JsonPropertyName("user")]
    public UserInfo User { get; set; } = new();
}

public class Workstation
{
    [JsonPropertyName("id")]
    public int Id { get; set; }

    [JsonPropertyName("code")]
    public string Code { get; set; } = "";

    [JsonPropertyName("label")]
    public string Label { get; set; } = "";
}

public class TimeEntry
{
    [JsonPropertyName("id")]
    public int Id { get; set; }

    [JsonPropertyName("entry_date")]
    public string EntryDate { get; set; } = "";

    [JsonPropertyName("period")]
    public string Period { get; set; } = "";

    [JsonPropertyName("slot")]
    public int Slot { get; set; }

    [JsonPropertyName("workstation_id")]
    public int WorkstationId { get; set; }

    [JsonPropertyName("workstation_code")]
    public string WorkstationCode { get; set; } = "";

    [JsonPropertyName("workstation_label")]
    public string WorkstationLabel { get; set; } = "";
}

public class TimeEntryRequest
{
    [JsonPropertyName("date")]
    public string Date { get; set; } = "";

    [JsonPropertyName("period")]
    public string Period { get; set; } = "";

    [JsonPropertyName("slot")]
    public int Slot { get; set; }

    [JsonPropertyName("workstationId")]
    public int WorkstationId { get; set; }
}

public class SaveEntriesRequest
{
    [JsonPropertyName("entries")]
    public List<TimeEntryRequest> Entries { get; set; } = new();
}

public class StatsResponse
{
    [JsonPropertyName("from")]
    public string From { get; set; } = "";

    [JsonPropertyName("to")]
    public string To { get; set; } = "";

    [JsonPropertyName("totalHours")]
    public decimal TotalHours { get; set; }

    [JsonPropertyName("byWorkstation")]
    public List<WorkstationStat> ByWorkstation { get; set; } = new();

    [JsonPropertyName("byDay")]
    public List<DayStat> ByDay { get; set; } = new();
}

public class WorkstationStat
{
    [JsonPropertyName("code")]
    public string Code { get; set; } = "";

    [JsonPropertyName("label")]
    public string Label { get; set; } = "";

    [JsonPropertyName("total_hours")]
    public decimal TotalHours { get; set; }
}

public class DayStat
{
    [JsonPropertyName("entry_date")]
    public string EntryDate { get; set; } = "";

    [JsonPropertyName("total_hours")]
    public decimal TotalHours { get; set; }
}

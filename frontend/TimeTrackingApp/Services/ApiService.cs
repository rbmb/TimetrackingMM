using System.Net.Http.Json;
using TimeTrackingApp.Models;

namespace TimeTrackingApp.Services;

public class ApiService
{
    private readonly HttpClient _http;

    public ApiService(HttpClient http)
    {
        _http = http;
    }

    // Workstations
    public async Task<List<Workstation>> GetWorkstationsAsync()
    {
        return await _http.GetFromJsonAsync<List<Workstation>>("workstations.php") ?? new();
    }

    // Time entries
    public async Task<List<TimeEntry>> GetEntriesAsync(DateOnly from, DateOnly to)
    {
        var url = $"time-entries.php?from={from:yyyy-MM-dd}&to={to:yyyy-MM-dd}";
        return await _http.GetFromJsonAsync<List<TimeEntry>>(url) ?? new();
    }

    public async Task<bool> SaveEntriesAsync(List<TimeEntryRequest> entries)
    {
        var request = new SaveEntriesRequest { Entries = entries };
        var response = await _http.PostAsJsonAsync("time-entries.php", request);
        return response.IsSuccessStatusCode;
    }

    public async Task<bool> DeleteEntryAsync(int id)
    {
        var response = await _http.DeleteAsync($"time-entries.php?id={id}");
        return response.IsSuccessStatusCode;
    }

    // Stats
    public async Task<StatsResponse?> GetStatsAsync(DateOnly from, DateOnly to)
    {
        var url = $"stats.php?from={from:yyyy-MM-dd}&to={to:yyyy-MM-dd}";
        return await _http.GetFromJsonAsync<StatsResponse>(url);
    }

    // Export URL (for download link)
    public string GetExportUrl(DateOnly from, DateOnly to)
    {
        return $"{_http.BaseAddress}export.php?from={from:yyyy-MM-dd}&to={to:yyyy-MM-dd}";
    }

    // Users (admin)
    public async Task<List<UserListItem>> GetUsersAsync()
    {
        return await _http.GetFromJsonAsync<List<UserListItem>>("users.php") ?? new();
    }

    public async Task<HttpResponseMessage> CreateUserAsync(CreateUserRequest request)
    {
        return await _http.PostAsJsonAsync("users.php", request);
    }

    public async Task<HttpResponseMessage> UpdateUserAsync(UpdateUserRequest request)
    {
        return await _http.PutAsJsonAsync("users.php", request);
    }

    public async Task<bool> DeactivateUserAsync(int id)
    {
        var response = await _http.DeleteAsync($"users.php?id={id}");
        return response.IsSuccessStatusCode;
    }
}

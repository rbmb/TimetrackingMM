using System.Net.Http.Json;
using Blazored.LocalStorage;
using TimeTrackingApp.Models;

namespace TimeTrackingApp.Services;

public class AuthService
{
    private readonly HttpClient _http;
    private readonly ILocalStorageService _localStorage;

    private const string TokenKey = "auth_token";
    private const string UserKey = "auth_user";

    public UserInfo? CurrentUser { get; private set; }
    public string? Token { get; private set; }
    public bool IsAuthenticated => !string.IsNullOrEmpty(Token);

    public event Action? OnAuthStateChanged;

    public AuthService(HttpClient http, ILocalStorageService localStorage)
    {
        _http = http;
        _localStorage = localStorage;
    }

    public async Task InitializeAsync()
    {
        Token = await _localStorage.GetItemAsStringAsync(TokenKey);
        CurrentUser = await _localStorage.GetItemAsync<UserInfo>(UserKey);

        if (!string.IsNullOrEmpty(Token))
        {
            _http.DefaultRequestHeaders.Authorization =
                new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", Token);
        }
    }

    public async Task<bool> LoginAsync(string username, string password)
    {
        var request = new LoginRequest { Username = username, Password = password };
        var response = await _http.PostAsJsonAsync("auth.php", request);

        if (!response.IsSuccessStatusCode)
            return false;

        var result = await response.Content.ReadFromJsonAsync<LoginResponse>();
        if (result == null)
            return false;

        Token = result.Token;
        CurrentUser = result.User;

        await _localStorage.SetItemAsStringAsync(TokenKey, Token);
        await _localStorage.SetItemAsync(UserKey, CurrentUser);

        _http.DefaultRequestHeaders.Authorization =
            new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", Token);

        OnAuthStateChanged?.Invoke();
        return true;
    }

    public async Task LogoutAsync()
    {
        Token = null;
        CurrentUser = null;

        await _localStorage.RemoveItemAsync(TokenKey);
        await _localStorage.RemoveItemAsync(UserKey);

        _http.DefaultRequestHeaders.Authorization = null;
        OnAuthStateChanged?.Invoke();
    }
}

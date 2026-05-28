using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Authentication.JwtBearer;
using System;
using System.Linq;
using System.Collections.Generic;

namespace lab8.Controllers
{
    [Route("api/[controller]")]
    [ApiController]
    public class NumberController : ControllerBase
    {
        private static readonly List<int> PrimeNumbers = new List<int> { 2, 3, 5, 7, 11, 13 };
        private readonly Random _random = new Random();

        [Authorize(AuthenticationSchemes = JwtBearerDefaults.AuthenticationScheme, Roles = "number")]
        [HttpGet("drawprime")]
        public IActionResult DrawPrimeNumber()
        {
            int index = _random.Next(PrimeNumbers.Count);
            int drawnNumber = PrimeNumbers[index];
            return Ok(new { drawnNumber = drawnNumber });
        }
    }
}
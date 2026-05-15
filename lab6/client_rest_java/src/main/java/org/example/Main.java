package org.example;

import org.json.JSONArray;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.URL;
import java.util.ArrayList;
import java.util.List;
import java.util.stream.Collectors;

public class Main {
    public static void main(String[] args) {
        try {
            String temp_url = "http://localhost/server_rest_php/cities/read";
            URL url = new URL(temp_url);
            System.out.println("Wysyłanie zapytania...");
            InputStream is = url.openStream();
            System.out.println("Pobieranie odpowiedzi...");
            String source = new BufferedReader(new
                    InputStreamReader(is))
                    .lines().collect(Collectors.joining("\n"));
            System.out.println("Przetwarzanie danych...");
            JSONObject json = new JSONObject(source);
            JSONArray receivedData = (JSONArray) json.get("cities");

            List<City> cities = new ArrayList<>();
            for (int i = 0; i < receivedData.length(); i++) {
                JSONObject obj = receivedData.getJSONObject(i);
                City city = new City(
                        obj.getInt("ID"),
                        obj.getString("Name"),
                        obj.getString("CountryCode"),
                        obj.getString("District"),
                        obj.getInt("Population")
                );
                cities.add(city);
            }

            CityPrinter.printAllCities(cities);

        } catch (Exception e) {
            System.err.println("Wystąpił nieoczekiwany błąd!!!");
            e.printStackTrace(System.err);
        }
    }
}
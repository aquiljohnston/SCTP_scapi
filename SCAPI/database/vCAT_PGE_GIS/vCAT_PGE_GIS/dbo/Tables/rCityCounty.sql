CREATE TABLE [dbo].[rCityCounty] (
    [CityCountyID] INT            IDENTITY (1, 1) NOT NULL,
    [City]         NVARCHAR (255) NULL,
    [County]       NVARCHAR (255) NULL,
    [CountyCode]   NVARCHAR (255) NULL,
    CONSTRAINT [PK_rCityCounty] PRIMARY KEY CLUSTERED ([CityCountyID] ASC)
);






GO
CREATE NONCLUSTERED INDEX [NonClusteredIndex-City]
    ON [dbo].[rCityCounty]([City] ASC);


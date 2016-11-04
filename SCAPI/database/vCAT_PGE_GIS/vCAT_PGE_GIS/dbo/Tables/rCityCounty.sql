CREATE TABLE [dbo].[rCityCounty] (
    [City]       NVARCHAR (255) NULL,
    [County]     NVARCHAR (255) NULL,
    [CountyCode] NVARCHAR (255) NULL
);




GO
CREATE NONCLUSTERED INDEX [NonClusteredIndex-City]
    ON [dbo].[rCityCounty]([City] ASC);

